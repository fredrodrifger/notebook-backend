## چه چیزی عوض شد؟

<!-- یک پاراگراف: مسئله، راه‌حل، و کدام لایه‌ها را لمس کرد -->

## فایل‌ها به تفکیک لایه

<!-- migration / model+interface / repository / request+rule / controller / resource / action / test / doc -->

## چطور تست شد؟

```bash
./vendor/bin/pint --test
php artisan test
```

<!-- خروجی دقیق. اگر چیزی اجرا نشد، صریح بنویس -->

## چک‌لیست

- [ ] ثابت‌ها/اینترفیس، migration و `$casts` هم‌زمان به‌روز شده
- [ ] `pint --test` پاس (فرمت‌دهی فقط روی خطوط تغییریافته)
- [ ] تست رفتار تغییریافته در همین PR
- [ ] پاکت پاسخ دست‌نخورده: `{response, status}` / خطا `{message, errors}`
- [ ] سبک با نزدیک‌ترین فایل خواهر یکی است (`docs/style-contract.md`)
- [ ] اثر migration و هر گام استقرار (scheduler/queue/cache) در متن PR آمده
- [ ] ریسک/بازگردانی در یک-دو خط

## مربوط به

Closes #
