# راهنمای مشارکت — بک‌اند

## قاعدهٔ طلایی

کد از **سبک تیم** تبعیت می‌کند. مرجع: `docs/style-pack/ali-mousavi-backend-style/` و
خلاصهٔ اجرایی در `docs/style-contract.md`. قبل از نوشتن هر فایل، نزدیک‌ترین فایل خواهر را پیدا کن و آینه کن.

## جریان کار

```
main                     ← همیشه سبز (pint + php artisan test)
 └─ <type>/<short-topic> ← شاخهٔ کار
     └─ Pull Request      ← با بخش «چطور تست شد» و گزارش «چه چیزی اجرا نشد»
```

`feat/…`, `fix/…`, `chore/…`, `docs/…` — مستقیم روی `main` کامیت نکن.

## ترتیب کار (سبک خودمان)

`constants/interface → model + cast + relation trait → migration → repository → request + rule →
controller + policy → resource → test → doc`

## کامیت‌ها

Conventional Commits، lowercase، امری، کوتاه، **یک رفتار در هر کامیت**:

```text
feat: notes resource for the notebook api
fix: scope note search to the title column
```

فرمت‌دهی فقط روی خطوطی که تغییر داده‌ای؛ فرمت‌کردن کل ریپو داخل یک تغییر رفتاری ممنوع.
نویسندگی گیت هرگز به نام برنامه‌نویس سبک ست نمی‌شود.

## چک‌لیست قبل از PR

- [ ] `./vendor/bin/pint --test` پاس
- [ ] `php artisan test` پاس (تست رفتار تغییریافته در همان PR)
- [ ] هر ستون جدید در اینترفیس (ثابت)، migration و `$casts` هم‌زمان اضافه شده
- [ ] پاکت پاسخ دست‌نخورده مانده: `{response, status}` / خطا `{message, errors}`
- [ ] نام ستون‌ها در کد از ثابت‌ها می‌آید، نه رشتهٔ خام
- [ ] گزارش دقیق دستورها و خروجی‌ها؛ هر بررسی که انجام نشده صریح نوشته شود
