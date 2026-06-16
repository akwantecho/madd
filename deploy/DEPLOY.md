# دليل ربط الدومين ونشر نظام Event Puls على VPS

هذا الدليل ينشر النظام على خادم VPS فارغ، يربط دومين **Squarespace**، ويفعّل **HTTPS مجاناً** عبر Let's Encrypt.

> **ملاحظة عن SSL:** Squarespace توفّر شهادة SSL فقط للمواقع المستضافة لديها. بما أننا نستضيف على VPS، سنُصدر شهادة مجانية من **Let's Encrypt** على الخادم مباشرة (المرحلة 5). تتجدد تلقائياً كل 90 يوماً.

قبل أن تبدأ، جهّز:
- **عنوان IP** الخاص بالخادم (من لوحة مزوّد VPS).
- **بيانات SSH** (المستخدم غالباً `root` + كلمة مرور أو مفتاح).
- **دومينك** من Squarespace.

---

## المرحلة 0 — الدخول للخادم ومعرفة نظامه

من جهازك (Terminal على الماك)، ادخل عبر SSH (استبدل `IP_ADDRESS`):

```bash
ssh root@IP_ADDRESS
```

بعد الدخول، اعرف نظام التشغيل:

```bash
cat /etc/os-release | head -2
```

- إن ظهر **Ubuntu** أو **Debian** → تابع، فالسكربت يدعمهما.
- إن ظهر نظام آخر → أخبرني به.

---

## المرحلة 1 — ربط DNS عند Squarespace

1. ادخل [account.squarespace.com](https://account.squarespace.com) → **Domains** → اختر دومينك.
2. افتح **DNS Settings** (إعدادات DNS).
3. **احذف** أي سجلّات `A` أو `CNAME` افتراضية تشير إلى Squarespace (Parking page).
4. أضِف سجلّين جديدين (استبدل `IP_ADDRESS` بعنوان خادمك):

   | Host (Name) | Type | Data (Value)  |
   |-------------|------|---------------|
   | `@`         | A    | `IP_ADDRESS`  |
   | `www`       | A    | `IP_ADDRESS`  |

5. احفظ. **الانتشار يستغرق من دقائق إلى ساعات.** تحقّق من جهازك:

   ```bash
   dig +short example.com
   ```
   يجب أن يُرجع `IP_ADDRESS`. لا تتابع المرحلة 5 (SSL) قبل ظهوره.

---

## المرحلة 2 — تشغيل سكربت النشر التلقائي

**طريقة (أ) — رفع السكربت من جهازك مباشرة (تعمل فوراً):**
من جهازك المحلي (Terminal، وأنت داخل مجلد المشروع):
```bash
scp deploy/setup.sh root@IP_ADDRESS:/root/setup.sh
```
ثم ادخل الخادم وتابع من خطوة `nano setup.sh` أدناه.

**طريقة (ب) — التنزيل من GitHub** (تعمل فقط بعد دفع مجلد `deploy/` إلى فرع `main`):

على **الخادم** (وأنت داخل SSH):

```bash
# نزّل السكربت من المستودع
curl -sO https://raw.githubusercontent.com/akwantecho/madd/main/deploy/setup.sh

# عدّل القيم الثلاثة في أعلى الملف (الدومين/المستودع/المسار)
nano setup.sh

# شغّله
sudo bash setup.sh
```

في `nano`: غيّر `DOMAIN="example.com"` إلى دومينك، ثم `Ctrl+O` ثم `Enter` للحفظ و`Ctrl+X` للخروج.

السكربت يثبّت Nginx + PHP 8.3 + Composer، يستنسخ المشروع، يُنشئ ملف `.env` للإنتاج، يولّد `APP_KEY`، يشغّل الهجرات (migrations)، يضبط الصلاحيات والكاش، ويُعدّ Nginx. في النهاية يعمل الموقع عبر **HTTP**.

افتح في المتصفح: `http://example.com` — يجب أن تظهر لوحة التحكم.

---

## المرحلة 3 — تفعيل HTTPS (شهادة SSL مجانية)

**بعد** التأكد من أن DNS انتشر (المرحلة 1)، نفّذ على الخادم:

```bash
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot --nginx -d example.com -d www.example.com
```

- أدخل بريدك الإلكتروني عند الطلب، ووافق على الشروط.
- عند سؤال إعادة التوجيه، اختر **Redirect** (تحويل كل HTTP إلى HTTPS).

الآن افتح: `https://example.com` 🔒 — ويُفترض أن يعمل بقفل آمن.

التجديد تلقائي؛ للتأكد:
```bash
sudo certbot renew --dry-run
```

---

## التحديثات اللاحقة (نشر تغييرات جديدة)

عند تعديل الكود ودفعه إلى GitHub، حدّث الخادم بـ:

```bash
cd /var/www/eventpuls
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache database
```

---

## حل المشكلات الشائعة

| المشكلة | الحل |
|---------|------|
| `502 Bad Gateway` | تأكد أن مسار سوكِت PHP-FPM في إعداد Nginx يطابق إصدارك: `ls /run/php/`. |
| صفحة بيضاء / `500` | راجع السجل: `tail -n 50 /var/www/eventpuls/storage/logs/laravel.log`، وتأكد من صلاحيات `storage`. |
| `certbot` يفشل | DNS لم ينتشر بعد، أو المنفذ 80 مغلق. تأكد من `dig +short example.com` ومن `ufw allow 'Nginx Full'`. |
| خطأ قاعدة البيانات | تأكد من وجود `database/database.sqlite` وأنه مملوك لـ `www-data` بصلاحية `664`. |
| الموقع يظهر بالإنجليزية | غيّر `APP_LOCALE=ar` في `.env` ثم `php artisan config:cache`. |

> أبلغني بعنوان IP ودومينك الفعليين إن أردت أن أملأ السكربت والأوامر لك جاهزة للّصق.
