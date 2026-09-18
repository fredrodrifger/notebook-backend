# قرارداد سبک — بک‌اند

پاسخ به این سؤال: «کدام سبک رعایت می‌شود، و کجا آگاهانه فاصله گرفتیم؟»

## ۱. منبع

پک `ali-mousavi-backend-style` v1 (`docs/style-pack/`) — ساخته‌شده از ~۵۵۲ رکورد کامیت منتسب و
خواندن خط‌به‌خط ۶ تغییر در بک‌اند چند-مستأجری Fusion (Laravel 13، PHP 8.3، `stancl/tenancy`).
پک خودش تصریح می‌کند که «راهنمای سازگاری» است، نه بازتولید کامل یک شخص.

## ۲. الگوهایی که رعایت می‌شوند

| الگو | جای پیاده‌سازی در این پروژه |
|---|---|
| مدل + اینترفیس: اینترفیس ثابت‌ها و قراردادها، مدل traitهای همراه | `app/Interfaces/Models/Note/NoteInterface.php` + `app/Models/Note/Note.php` |
| ثابت به‌جای رشتهٔ ستون؛ `getX()/setX()` از طریق `MagicMethodsTrait` | `app/Models/BaseModel.php` + `app/Traits/MagicMethodsTrait.php` |
| `BaseRepository` با `query()`، `create/update` و **`index($user)`** به‌عنوان تنها ورودی فهرست‌ها | `app/Repositories/BaseRepository.php` |
| فیلترهای اعلانی روی `FilterAbstract` فیلوکوئنت (`filterables`/`searchables`/`orderables`) | `app/Filters/NotesFilter.php` |
| `BaseRequest` با `authorize() → true`، `safeValidated()`، `perPage()` سقف‌دار | `app/Http/Requests/BaseRequest.php` |
| قواعد سفارشی در `app/Rules/<Domain>/` با early-return | `app/Rules/Note/*` |
| کنترر با تزریق repository در سازنده، یک خط هماهنگی در هر اکشن، `authorize` روی هر اکشن شیئی | `app/Http/Controllers/Note/NotesController.php` |
| پاکت `{response, status}` از `JsonResource::wrap(Controller::RESPONSE)` + `SuccessStatusTrait` | `app/Http/Controllers/Controller.php` + `app/Http/Resources/BaseResource.php` |
| خروجی آگاه از مخاطب: `getCommonArray()` + آرایه‌های نقش‌محور (`getAdminArray`…) | `app/Http/Resources/NoteResource.php` |
| عملیات نام‌دار در `app/Actions/<Domain>/` با `handle()`، صدا زدن با `run(new XAction)` | `app/Actions/` + `app/helpers.php` |
| migration با نام ستون از ثابت‌ها + `SoftDeletes` | `database/migrations/` |
| تست در همان تغییر: PHPUnit، `tests/Unit` + `tests/Feature`، SQLite در حافظه، نام‌های رفتاری | `tests/` |
| فرمت: Pint preset laravel + `class_definition.multi_line_extends_each_single_line`؛ ۴ فاصله، LF، تک‌کوتیشن، `'a'.$b`، `! $x`، `new X` بی‌پرانتز | `pint.json` + `.editorconfig` |
| ایمپورت‌ها الفبایی بر اساس namespace کامل | همهٔ فایل‌ها |
| کامیت: lowercase، کوتاه، امری، یک رفتار؛ مستند اگر payload عوض شده (همان PR) | تاریخچهٔ همین ریپو |

## ۳. فاصله‌های آگاهانه (طبق `prestige-boundaries.md` و تصمیم‌های کارفرما)

۱. **تک‌مستأجری:** `stancl/tenancy`، `InitializeTenantMiddleware`، کانکشن `tenant`، مسیر
   `{company}/{locale}`، `init()`/`tenant_config()` و migrationهای `database/migrations/tenant`
   پیاده **نمی‌شوند** — چون این اپ یک دیتابیس و یک کاربر دارد. در عوض `ConnectionConstants`
   با یک کانکشن `app` می‌ماند تا مدل‌ها اتصال خود را صریح اعلام کنند (قاعدهٔ پک).
۲. **بدون احراز هویت و Policy نقش‌محور:** اپ تک‌نفرهٔ محلی است. بنابراین `auth.user`،
   `HasAccessToLocationsRule` و آرایه‌های نقش‌محور منبع **فقط یک `getCommonArray`** می‌ماند؛
   اما امضای `index($user)` با کاربر `null` نگه داشته می‌شود تا شکل معماری تیم حفظ شود.
۳. **نسخه:** بک‌اند تیم Laravel 13 روی PHP 8.3 است؛ این ماشین PHP 8.2.33 دارد و تصمیم گرفته شد
   مخازن سیستم تغییر نکند → **Laravel 12.69**. تفاوت نسخه در این دامنه صفر است؛ اگر بعداً روی
   سرور با PHP 8.3 اجرا شود، `composer update` به Laravel 13 ارتقا می‌دهد.
۴. **بدون اینتگریشن‌های دامنهٔ رستوران** (QuickBooks/Stripe/Horizon/Telegram/…) — بند پک.
۵. **بدون `LiveCommandAbstract` و run-recordهای دو-کانکشنی** — نیازمند تقسیم central/tenant است.
۶. **صفحه‌بندی:** `DEFAULT_PAGE_SIZE` روی `10` تنظیم می‌شود (اپ نوتبوک صفحه‌های کوتاه‌تر دارد)
   در حالی که پک `15` را نقل می‌کند؛ سقف `perPage()` همچنان اعمال می‌شود.

## ۴. تله‌هایی که از پک یاد گرفتیم و در آن نمی‌افتیم

- `authorize() { return true; }` روی Request به‌معنای «بدون امنیت» نیست — بررسی شیئی جای دیگری است
  (در این پروژه چون لاگین نیست، بررسی شیئی وجود ندارد و صریح مستند شده).
- `SerializesModels` به‌تنهایی تضمین جداسازی مستأجر نیست.
- `run()` **همگام** است؛ اکشن را «صف‌شده» توصیف نمی‌کنیم.
- فرمت‌کردن سراسری داخل یک تغییر رفتاری انجام نمی‌شود.
- هیچ نتیجهٔ build/test‌ای که اجرا نشده گزارش نمی‌شود.
