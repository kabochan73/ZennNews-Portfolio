#!/bin/sh
set -e

# Railway が動的に割り当てるポートに nginx を合わせる（未設定時は80）
PORT=${PORT:-80}
sed -i "s/listen 80;/listen ${PORT};/" /etc/nginx/nginx.conf

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec supervisord -c /etc/supervisord.conf
