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

## اجرای لایو (روی دامنه)

```bash
cp .env.example .env && php artisan key:generate    # اگر .env نیست
cat > .env.live <<EOF
NOTEBOOK_HOST=0.0.0.0
NOTEBOOK_PORT=5353
APP_ENV=production
APP_DEBUG=false
NOTEBOOK_API_TOKEN=$(openssl rand -hex 24)
EOF
bash scripts/deploy-live.sh
```

`scripts/deploy-live.sh` فرانت‌اند را build می‌کند، فایل‌های `dist` را در `public/` منتشر می‌کند
(تا UI و API روی یک مبدأ و یک پورت باشند)، `migrate --force` می‌زند و سرویس را روی
`NOTEBOOK_HOST:NOTEBOOK_PORT` بالا می‌آورد. مسیرهای `/api/*` با هدر `X-Notebook-Token`
محافظت می‌شوند؛ `/api/health` باز می‌ماند تا اسکریپت‌های اجرا آمادگی را بسنجند.

- اگر `.env.live` نباشد یا توکنش خالی باشد، اسکریپت **عمداً** بالا نمی‌آید.
- در حالت production، توکن خالی باعث رد شدن همهٔ درخواست‌ها می‌شود (نه باز شدن API).
- `/public/index.html` و `/public/assets` ساختهٔ build هستند و در گیت نیستند.

## بررسی پیش از PR

```bash
./vendor/bin/pint --test      # فرمت: preset laravel
php artisan test              # سوئیت Unit + Feature روی SQLite
```
