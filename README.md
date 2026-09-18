# دفترچه یادداشت — بک‌اند

سرویس محلی یادداشت‌ها برای نوتبوک تکنفره. Laravel 12 روی SQLite، بدون احراز هویت، تک‌مستأجری.
مصرف‌کننده: ریپوی **[notebook-frontend](https://github.com/fredrodrifger/notebook-frontend)**.

> **قاعدهٔ اصلی:** کد با سبک تیم خودمان نوشته می‌شود، نه با سلیقهٔ ایجنت.
> مرجع سبک: `docs/style-pack/ali-mousavi-backend-style/` و خلاصهٔ اجرایی در `docs/style-contract.md`.
> قرارداد داده با فرانت‌اند: `docs/api-contract.md`.

## استک

PHP 8.3 در بک‌اند تیم · این ماشین Laravel 12.69 روی PHP 8.2.33 (تصمیم صریح: بدون تغییر مخازن سیستم)
Laravel 12 · `alimousavi/filoquent` (فیلترهای اعلانی) · SQLite · Pint (preset laravel) · PHPUnit 12

## وضعیت

| بخش | وضعیت |
|---|---|
| اسکلت Laravel + Pint + فیلوکوئنت + docs + CI | ✅ |
| مدل/اینترفیس ثابت‌ها + migration یادداشت‌ها | ⏳ |
| Repository + Filter + Resource + Controller + Requests + Rules | ⏳ |
| تست‌های پذیرش قرارداد (۱۰ بررسی) | ⏳ |
| اجرای سرویس و تست واقعی با curl | ⏳ |

## اجرا

```bash
bash scripts/serve.sh          # محیط را آماده می‌کند، migrate می‌زند، سرویس را بالا می‌آورد
```

یا دستی:

```bash
cp .env.example .env          # اگر .env ندارید
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

بررسی سلامت: `curl -s http://127.0.0.1:8000/api/health`

برای بالا آوردن هم‌زمان بک‌اند و فرانت‌اند، در ریپوی فرانت‌اند `npm run dev:all` را بزنید
(اسکریپت `scripts/dev-all.sh` این ریپو را از مسیر خواهر پیدا می‌کند و بعد از آماده شدن
`/api/health` سرویس UI را اجرا می‌کند).

## بررسی پیش از PR

```bash
./vendor/bin/pint --test      # فرمت: preset laravel
php artisan test              # سوئیت Unit + Feature روی SQLite
```
