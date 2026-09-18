# قرارداد API — بین بک‌اند و فرانت‌اند

نسخه: ۲ (پاکت Laravel تیم) · مالک سند: بک‌اند · مصرف‌کننده: `notebook-frontend/src/api/notes.ts`

## ۱. قواعد کلی

- پیشوند همهٔ مسیرها `/api`؛ مسیرها با `apiResource` + روت‌های اضافه تعریف می‌شوند،
  نه با افعال سفارشی (`batchDestroy`, `restore`, `duplicate` الگوهای مجازند).
- احراز هویت نداریم (اپ تک‌نفرهٔ محلی)؛ هیچ گاردی روی این مسیرها نیست.
- فیلدهای داده **snake_case**؛ کلیدها در کد از ثابت‌های اینترفیس مدل می‌آیند.
- شناسه‌ها UUID هستند و binding با `{note:uuid}` انجام می‌شود.
- تاریخ‌ها ISO-8601 با منطقهٔ زمانی UTC: `2026-09-18T13:55:48Z` (Laravel `toIso8601String()`).
- صفحه‌بندی: پارامتر `page` **یک‌مبنا** (پیش‌فرض ۱) و `per_page` که در `BaseRequest::perPage()`
  به `DEFAULT_PAGE_SIZE = 15` محدود می‌شود.

## ۲. پاکت پاسخ (تغییرناپذیر)

| حالت | شکل | منبع در کد |
|---|---|---|
| تک‌آیتم (show/store/update) | `{ "response": { … }, "status": 200 }` | `JsonResource::wrap(Controller::RESPONSE)` + `SuccessStatusTrait` |
| فهرست صفحه‌بندی‌شده | `{ "response": { "data": [ … ], "meta": { … }, "links": { … } }, "status": 200 }` | `Resource::collection(…->paginate())->additional([Controller::STATUS => 200])` |
| فهرست ساده (تگ‌ها) | `{ "response": [ … ], "status": 200 }` | `sendResponse()` |
| حذف | `{ "message": "Deleted successfully", "status": 200 }` | `deleted()` |
| حذف گروهی | `{ "message": "Deleted successfully", "count": 3, "status": 200 }` | `batchDeleted($count)` |
| خطای اعتبارسنجی | `{ "message": "…", "errors": { "title": ["…"] } }` (۴۲۲) | Laravel + `sendErrorResponse()` |
| خطای دیگر | `{ "message": "…", "errors": [], "response": null }` (۴۰۴/۵۰۰) | `sendErrorResponse()` |

کلیدهای `response` و `status` مصرف‌کننده دارند: **حتی با تغییر نام متدهای داخلی، عوض نمی‌شوند.**

## ۳. مدل Note

```json
{
  "id": "9a1f7c1e-8f5f-4a44-9d2c-1f0a1b7d5e10",
  "title": "عنوان یادداشت",
  "content": "<p>متن ادیتور (HTML خروجی TipTap)</p>",
  "tags": ["کار", "ایده"],
  "is_pinned": false,
  "created_at": "2026-09-18T13:55:48+00:00",
  "updated_at": "2026-09-18T13:55:48+00:00"
}
```

## ۴. اندپوینت‌ها

### `GET /api/notes` — فهرست

پارامترها (همه اختیاری، همه در `NotesFilter` اعلان‌شده — پارامتر اعلان‌نشده نادیده گرفته می‌شود):
`page`، `per_page`، `search` (روی `title` و `content`)، `tag`، `isPinned`، `orderBy`

نام پارامترها قرارداد `filoquent` است: فیلترها با همان کلیدِ اعلان‌شده در `filterables` خوانده
می‌شوند (camelCase) و مرتب‌سازی با `orderBy=field:direction` و در صورت چند ستون با کاما:
`orderBy=is_pinned:desc,updated_at:desc`. ستون‌هایی که در `orderables` اعلان نشده باشند نادیده می‌مانند.

```json
{
  "response": {
    "data": [ { "id": "…", "title": "…", "tags": [], "is_pinned": true, "…": "…" } ],
    "meta": { "current_page": 1, "per_page": 10, "total": 42, "last_page": 5, "from": 1, "to": 10 },
    "links": { "first": "…", "last": "…", "prev": null, "next": "…" }
  },
  "status": 200
}
```

مرتب‌سازی پیش‌فرض: `is_pinned` نزولی، سپس `updated_at` نزولی.

### `POST /api/notes` — ساخت

```json
{ "title": "عنوان", "content": "<p>…</p>", "tags": ["کار"] }
```

- `title` اجباری و غیرتهی، حداکثر ۲۵۵ کاراکتر · `content` اختیاری · `tags` اختیاری (آرایهٔ رشته، هر تگ ≤ ۳۲ کاراکتر)
- پاسخ: **۲۰۰** (قاعدهٔ تیم: ساخت هم `200` می‌دهد، نه ۲۰۱) با `{ "response": { …Note… }, "status": 200 }`

### `GET /api/notes/{uuid}` — یک یادداشت

`{ "response": { …Note… }, "status": 200 }` · شناسهٔ ناموجود → ۴۰۴ با `{ "message": "…" }`

### `PATCH /api/notes/{uuid}` — ویرایش

هر زیرمجموعه‌ای از `{ title, content, tags, is_pinned }` با معنای **«غایب = دست‌نخورده،
حاضر و خالی = پاک‌شده»** (قاعدهٔ `sometimes` + `$request->has(...)` بک‌اند تیم).
`updated_at` در سرور به‌روز می‌شود. پاسخ: `{ "response": { …Note… }, "status": 200 }`

### `DELETE /api/notes/{uuid}` — حذف

`{ "message": "Deleted successfully", "status": 200 }` · شناسهٔ ناموجود → ۴۰۴

### `DELETE /api/notes` — حذف گروهی

بدنه: `{ "ids": ["uuid", "uuid"] }` → `{ "message": "Deleted successfully", "count": 2, "status": 200 }`

### `GET /api/tags` — تگ‌های موجود

`{ "response": ["کار", "ایده"], "status": 200 }`

### `GET /api/notes/stats` — آمار (کنترلر invokable)

```json
{ "response": { "notes_count": 42, "tags_count": 5, "last_updated_at": "2026-09-18T13:55:48+00:00" }, "status": 200 }
```

### `GET /api/health` — سلامت سرویس (برای تست محلی و اسکریپت اجرا)

```json
{ "response": { "state": "ok", "database": "ok", "uptime_seconds": 12.3 }, "status": 200 }
```

## ۵. تست‌های پذیرش (باید به‌صورت تست PHPUnit و سپس curl واقعی اجرا شوند)

۱. `GET /api/health` → `200`، `response.state = ok` و `response.database = ok`
۲. `POST /api/notes` بدنهٔ درست → `200`، `response.id` و `response.title` درست
۳. `POST /api/notes` با `title` خالی → `422` و `errors.title` موجود
۴. `POST /api/notes` با `title` خالی‌شده از فاصله → `422`
۵. `GET /api/notes` → یادداشت ساخته‌شده در `response.data`، `response.meta.total` درست
۶. `GET /api/notes/{uuid}` → همان یادداشت؛ شناسهٔ ناموجود → `404`
۷. `PATCH /api/notes/{uuid}` با `{"is_pinned": true}` → `response.is_pinned = true` و `updated_at` جلو می‌رود
۸. `GET /api/notes?search=…` فقط منطبق‌ها را بدهد؛ `?tag=…` فقط همان تگ
۹. صفحه‌بندی: با ۱۲ یادداشت و `per_page=10`، صفحهٔ ۲ فقط ۲ آیتم و `meta.total = 12` بدهد
۱۰. `DELETE /api/notes/{uuid}` → `200`، و `GET` همان شناسه بعد از آن `404` بدهد
۱۱. پایداری: پس از ری‌استارت سرویس، داده‌ها روی دیسک باقی بمانند
۱۲. `PATCH` با `{"tags": []}` تگ‌ها را خالی کند، و `PATCH` بدون کلید `tags` آن‌ها را دست‌نخورده بگذارد
