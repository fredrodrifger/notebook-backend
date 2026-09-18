# قرارداد API — بین بک‌اند و فرانت‌اند

نسخه: ۳ (پاکت Laravel تیم، هم‌راستا با پیاده‌سازی واقعی) · مالک سند: بک‌اند
مصرف‌کننده: `notebook-frontend/src/api/notes.ts`

## ۱. قواعد کلی

- پیشوند همهٔ مسیرها `/api`؛ مسیرها با `apiResource` + روت‌های اضافه (`batchDestroy`, `duplicate`)
  تعریف می‌شوند، نه با افعال سفارشی.
- احراز هویت نداریم (اپ تک‌نفرهٔ محلی)؛ هیچ گاردی روی این مسیرها نیست.
- فیلدهای داده **snake_case**؛ کلیدهای پاسخ از ثابت‌های اینترفیس مدل می‌آیند.
- شناسهٔ عمومی **UUID** است؛ در URL با `{note}` و binding پیش‌فرض (`Note::getRouteKeyName()`)
  و در بدنه با کلید `uuid`. کلید عددی داخلی **در پاسخ نیست**.
- تاریخ‌ها ISO-8601 با منطقهٔ زمانی: `2026-09-18T14:15:34+00:00`.
- صفحه‌بندی: `page` یک‌مبنا (پیش‌فرض ۱) و `per_page` که در `BaseRequest::perPage()`
  به `Controller::DEFAULT_PAGE_SIZE = 10` سقف‌گذاری می‌شود (کمتر مجاز، بیشتر نه).

## ۲. پاکت پاسخ (تغییرناپذیر)

| حالت | شکل | منبع در کد |
|---|---|---|
| تک‌آیتم (show / store / update / duplicate) | `{ "response": { … }, "status": 200 }` | `JsonResource::wrap(Controller::RESPONSE)` + `SuccessStatusTrait` |
| فهرست صفحه‌بندی‌شده | `{ "response": [ …rows… ], "links": { … }, "meta": { … }, "status": 200 }` | `Resource::collection(…->paginate())->additional([Controller::STATUS => 200])` |
| فهرست ساده (تگ‌ها) | `{ "response": [ … ], "status": 200 }` | `sendResponse()` |
| حذف | `{ "message": "Deleted successfully", "status": 200 }` | `deleted()` |
| حذف گروهی | `{ "message": "Deleted successfully", "count": 3, "status": 200 }` | `batchDeleted($count)` |
| خطای اعتبارسنجی | `{ "message": "…", "errors": { "title": ["…"] } }` (۴۲۲) | Laravel + `sendErrorResponse()` |
| خطای دیگر | `{ "message": "…", "errors": [], "response": null }` (۴۰۴/۵۰۰) | `sendErrorResponse()` |

**دو نکتهٔ اندازه‌گیری‌شده (نه فرضی):**

۱. در Laravel 12 با `JsonResource::wrap('response')`، کلیدهای صفحه‌بندی (`links`/`meta`)
   **هم‌سطح `response`** می‌آیند و `response` خودِ آرایهٔ ردیف‌ها است. این با توصیف پک
   («meta داخل response») فرق دارد؛ آنچه این سرویس واقعاً می‌دهد ملاک است.
۲. Laravel برای مدل تازه‌ساخته‌شده `201` می‌دهد. پاکت تیم برای نوشتن‌ها `200` است، پس
   `SuccessStatusTrait::toResponse()` **یک‌جا** کد انتقال را روی `200` پین می‌کند
   (نه در هر اکشن کنترلر).

## ۳. مدل Note

```json
{
  "uuid": "59343847-05f5-4152-b509-6cbe98ad8b8b",
  "title": "یادداشت اول",
  "content": "<p>متن ادیتور (HTML خروجی TipTap)</p>",
  "tags": ["کار", "ایده"],
  "is_pinned": false,
  "created_at": "2026-09-18T14:15:34+00:00",
  "updated_at": "2026-09-18T14:15:34+00:00"
}
```

## ۴. اندپوینت‌ها

### `GET /api/notes` — فهرست

پارامترها (همه اختیاری، همه در `NotesFilter` اعلان‌شده — پارامتر اعلان‌نشده نادیده گرفته می‌شود):
`page`، `per_page`، `search` (روی `title` و `content`)، `tag`، `isPinned`، `orderBy`

- نام پارامترها قرارداد `filoquent` است: فیلتر با همان کلیدِ اعلان‌شده در `filterables` خوانده
  می‌شود و مرتب‌سازی با `orderBy=field:direction` (چند ستون با کاما:
  `orderBy=is_pinned:desc,updated_at:desc`). ستون‌های اعلان‌نشده در `orderables` نادیده می‌مانند.
- `isPinned` فرم‌های `true/false`، `1/0`، `'true'/'false'` را می‌پذیرد (قرارداد truthy فیلوکوئنت).
- مرتب‌سازی پیش‌فرض: `is_pinned` نزولی، سپس `updated_at` نزولی.

```json
{
  "response": [
    { "uuid": "…", "title": "…", "tags": [], "is_pinned": true, "…": "…" }
  ],
  "links": { "first": "…", "last": "…", "prev": null, "next": null },
  "meta": {
    "current_page": 1, "per_page": 10, "total": 42, "last_page": 5,
    "from": 1, "to": 10, "path": "…", "links": []
  },
  "status": 200
}
```

### `POST /api/notes` — ساخت

```json
{ "title": "عنوان", "content": "<p>…</p>", "tags": ["کار"] }
```

- `title` اجباری، بدون فاصلهٔ اضافی در دو سر، حداکثر ۲۵۵ کاراکتر (`پک‌شده از فاصله → ۴۲۲`)
- `content` اختیاری · `tags` اختیاری؛ هر تگ trim می‌شود، تگ خالی حذف و تکراری‌ها یکی می‌شوند،
  هر تگ حداکثر ۳۲ کاراکتر
- پاسخ: **۲۰۰** با `{ "response": { …Note… }, "status": 200 }`

### `GET /api/notes/{uuid}` — یک یادداشت

`{ "response": { …Note… }, "status": 200 }` · شناسهٔ ناموجود → ۴۰۴ با `{ "message": "…" }`

### `PATCH /api/notes/{uuid}` — ویرایش

زیرمجموعه‌ای از `{ title, content, tags, is_pinned }` با معنای **«غایب = دست‌نخورده،
حاضر و خالی = پاک‌شده»**: فرستادن `"tags": []` تگ‌ها را پاک می‌کند و نبودِ کلید `tags`
آن‌ها را دست‌نخورده می‌گذارد. `updated_at` در سرور به‌روز می‌شود.

### `DELETE /api/notes/{uuid}` — حذف نرم

`{ "message": "Deleted successfully", "status": 200 }` · شناسهٔ ناموجود → ۴۰۴

### `DELETE /api/notes` — حذف گروهی

بدنه: `{ "ids": ["uuid", "uuid"] }` (هر شناسه باید موجود باشد، وگرنه ۴۲۲)
→ `{ "message": "Deleted successfully", "count": 2, "status": 200 }`

### `POST /api/notes/{uuid}/duplicate` — کپی (برای «پیست» در فهرست)

با `DuplicateNoteAction` کپی می‌سازد: عنوان + `" (copy)"`، تگ‌ها و متن یکسان، `is_pinned = false`،
UUID جدید. → `{ "response": { …Note… }, "status": 200 }`

### `GET /api/tags` — تگ‌های موجود

`{ "response": ["کار", "ایده"], "status": 200 }`

### `GET /api/notes/stats` — آمار (کنترلر invokable)

```json
{ "response": { "notes_count": 42, "tags_count": 5, "last_updated_at": "2026-09-18T14:15:34+00:00" }, "status": 200 }
```

### `GET /api/health` — سلامت سرویس (برای اسکریپت اجرا و تست محلی)

```json
{
  "response": {
    "state": "ok", "database": "ok", "notes_table": "notes",
    "laravel_version": "12.69.2", "php_version": "8.2.33",
    "checked_at": "2026-09-18T14:15:34+00:00"
  },
  "status": 200
}
```

## ۵. تست‌های پذیرش

این ۱۲ بررسی هم به‌صورت تست PHPUnit (`tests/Feature`, `tests/Unit`) و هم به‌صورت اجرای واقعی
با `curl` روی سرویس در حال اجرا انجام می‌شوند:

۱. `GET /api/health` → `200`، `response.state = ok`، `response.database = ok`
۲. `POST /api/notes` بدنهٔ درست → `200`، `response.uuid` و `response.title` درست
۳. `POST /api/notes` با `title` خالی → `422` و `errors.title`
۴. `POST /api/notes` با `title` فقط فاصله → `422`
۵. `GET /api/notes` → یادداشت ساخته‌شده در `response`، `meta.total` درست
۶. `GET /api/notes/{uuid}` → همان یادداشت؛ UUID ناموجود → `404`
۷. `PATCH /api/notes/{uuid}` با `{"is_pinned": true}` → `is_pinned` عوض شود و `updated_at` جلو برود
۸. `GET /api/notes?search=…` فقط منطبق‌ها؛ `?tag=…` فقط همان تگ
۹. صفحه‌بندی: با ۱۲ یادداشت و `per_page=10`، صفحهٔ ۲ دو ردیف و `meta.total = 12`
۱۰. `DELETE /api/notes/{uuid}` → `200`، و `GET` همان UUID بعد از آن `404`
۱۱. پایداری: پس از ری‌استارت سرویس، داده‌ها باقی بمانند (SQLite روی دیسک)
۱۲. `PATCH` با `{"tags": []}` تگ‌ها را خالی کند و `PATCH` بدون کلید `tags` آن‌ها را نگه دارد
