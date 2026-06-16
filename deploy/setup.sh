#!/usr/bin/env bash
#
# Event Puls — VPS provisioning + deploy script (Ubuntu/Debian).
# Run as root on a FRESH server:  sudo bash setup.sh
#
# It installs Nginx + PHP 8.3 + Composer, clones the app, configures the
# environment, runs migrations, sets permissions, and configures Nginx.
# SSL (HTTPS) is issued in a separate final step (see DEPLOY.md, المرحلة 5).
#
set -euo pipefail

# ─── EDIT THESE THREE VALUES ──────────────────────────────────────────────
DOMAIN="eventpluscamera.com"                           # دومينك بدون https://
REPO="https://github.com/akwantecho/madd.git"          # مستودع المشروع
APP_DIR="/var/www/eventpuls"                            # مسار التثبيت
# ──────────────────────────────────────────────────────────────────────────

PHP_VER="8.3"
echo "==> Event Puls deploy → ${DOMAIN}"

# 1) System packages -------------------------------------------------------
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y software-properties-common ca-certificates curl git unzip ufw

# PHP 8.3 PPA (Ubuntu). Harmless to skip on Debian if already 8.3.
if command -v add-apt-repository >/dev/null 2>&1; then
    add-apt-repository -y ppa:ondrej/php || true
    apt-get update -y
fi

apt-get install -y nginx \
    php${PHP_VER}-fpm php${PHP_VER}-cli php${PHP_VER}-mbstring php${PHP_VER}-xml \
    php${PHP_VER}-curl php${PHP_VER}-sqlite3 php${PHP_VER}-bcmath php${PHP_VER}-zip \
    php${PHP_VER}-gd php${PHP_VER}-intl

# 2) Composer --------------------------------------------------------------
if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# 3) Get the code ----------------------------------------------------------
if [ -d "${APP_DIR}/.git" ]; then
    echo "==> Repo exists, pulling latest"
    git -C "${APP_DIR}" pull --ff-only
else
    rm -rf "${APP_DIR}"
    git clone "${REPO}" "${APP_DIR}"
fi
cd "${APP_DIR}"

composer install --no-dev --optimize-autoloader

# 4) Environment -----------------------------------------------------------
if [ ! -f .env ]; then
cat > .env <<ENV
APP_NAME="Event Puls system"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://${DOMAIN}

APP_LOCALE=ar
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ar_SA

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
ENV
    php artisan key:generate
fi

# 5) Database (SQLite) + migrations ----------------------------------------
touch database/database.sqlite
php artisan migrate --force

# 6) Permissions -----------------------------------------------------------
chown -R www-data:www-data "${APP_DIR}"
find "${APP_DIR}" -type d -exec chmod 755 {} \;
find "${APP_DIR}" -type f -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache database
chmod 664 database/database.sqlite

# 7) Laravel caches --------------------------------------------------------
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8) Nginx site ------------------------------------------------------------
cat > /etc/nginx/sites-available/eventpuls <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${APP_DIR}/public;

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }

    client_max_body_size 20M;
}
NGINX

ln -sf /etc/nginx/sites-available/eventpuls /etc/nginx/sites-enabled/eventpuls
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl enable nginx php${PHP_VER}-fpm

# 9) Firewall --------------------------------------------------------------
ufw allow OpenSSH || true
ufw allow 'Nginx Full' || true
yes | ufw enable || true

echo ""
echo "============================================================"
echo " ✅ تم النشر عبر HTTP."
echo "    افتح:  http://${DOMAIN}"
echo ""
echo " 🔒 لتفعيل HTTPS (بعد ضبط DNS وانتشاره) شغّل:"
echo "    sudo apt-get install -y certbot python3-certbot-nginx"
echo "    sudo certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"
echo "============================================================"
