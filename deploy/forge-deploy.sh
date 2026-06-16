#!/usr/bin/env bash
#
# Event Puls — Forge Deploy Script
# ضعه في:  Forge → Server → Site → Apps/Deployment → "Deploy Script"
# ثم اضغط "Deploy Now". Forge يوفّر المتغيّرات $FORGE_* تلقائياً.
#
# هذا السكربت لا يعدّل أي ملف يدوياً — يسحب آخر كود من git ويعيد بناء الكاش،
# وهي الطريقة الآمنة التي لا تتعارض مع إدارة Forge للخادم.

cd $FORGE_SITE_PATH

# اسحب آخر تعديلات من الفرع الذي يتتبعه الموقع
git pull origin $FORGE_SITE_BRANCH

# ثبّت اعتماديات الإنتاج فقط
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# أعد تحميل PHP-FPM دون قطع الخدمة
( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

# شغّل الهجرات إن وُجدت جديدة (آمن — لا يوجد جديد في هذه التعديلات)
$FORGE_PHP artisan migrate --force

# أعد بناء كاش الإعدادات/المسارات/العروض (مهم: المسار الجديد /reports + ملف الترجمة)
$FORGE_PHP artisan config:cache
$FORGE_PHP artisan route:cache
$FORGE_PHP artisan view:cache
