#!/bin/sh
set -e

# Railway が動的に割り当てるポートに nginx を合わせる(未設定時は80)
PORT=${PORT:-80}
sed -i "s/listen 80;/listen ${PORT};/" /etc/nginx/nginx.conf

php artisan migrate --force

# config:cache は .env の値をbootstrap/cache/config.phpに焼き込んでしまい、
# 開発中の.env変更やphpunit.xmlのテスト用環境変数上書きが効かなくなる。
# 本番(Railway、APP_ENV=production)でのみキャッシュする。
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec supervisord -c /etc/supervisord.conf
