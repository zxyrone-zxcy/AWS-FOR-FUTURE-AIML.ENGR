#!/usr/bin/env bash
# Creates a fresh Laravel app and applies the MLA-C01 reviewer files on top of it.
# Usage: ./setup.sh [target-directory]
set -euo pipefail

HERE="$(cd "$(dirname "$0")" && pwd)"
APP="${1:-mla-c01-reviewer-app}"

command -v composer >/dev/null 2>&1 || { echo "Composer is required: https://getcomposer.org"; exit 1; }
command -v php >/dev/null 2>&1 || { echo "PHP 8.2+ is required"; exit 1; }
[ -e "$APP" ] && { echo "'$APP' already exists. Pick another name or remove it first."; exit 1; }

echo "==> Creating a fresh Laravel app in '$APP'"
composer create-project laravel/laravel "$APP" --prefer-dist --no-interaction

echo "==> Applying the reviewer files (routes, controller, views, assets, question bank, tests)"
cp -R "$HERE/overlay/." "$APP/"

cd "$APP"

echo "==> Configuring .env (no database needed: file sessions/cache)"
for f in .env .env.example; do
  [ -f "$f" ] || continue
  sed -i.bak \
    -e 's/^APP_NAME=.*/APP_NAME="AWS MLA-C01 Reviewer"/' \
    -e 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' \
    -e 's/^CACHE_STORE=.*/CACHE_STORE=file/' \
    -e 's/^CACHE_DRIVER=.*/CACHE_DRIVER=file/' \
    -e 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' \
    "$f"
  rm -f "$f.bak"
done

php artisan config:clear >/dev/null 2>&1 || true

cat <<EOF

Done. Next steps:
  cd $APP
  php artisan test        # sanity-check the pages and the question bank
  php artisan serve       # open http://127.0.0.1:8000

See README.md for deployment instructions.
EOF
