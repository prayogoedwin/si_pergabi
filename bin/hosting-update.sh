#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PHP_BIN="${PERGABI_PHP:-/opt/cpanel/ea-php84/root/usr/bin/php}"

if [[ ! -x "$PHP_BIN" ]]; then
    echo "PHP 8.4 tidak ditemukan: $PHP_BIN" >&2
    exit 1
fi

run_php() {
    # Hosting mematikan proc_open; artisan/composer butuh itu di CLI.
    "$PHP_BIN" -d disable_functions= "$@"
}

ensure_php84_handler() {
    local file="$1"
    [[ -f "$file" ]] || return 0
    if grep -q 'BEGIN PERGABI PHP84' "$file"; then
        return 0
    fi

    local tmp
    tmp="$(mktemp)"
    cat > "$tmp" << 'HDR'
# BEGIN PERGABI PHP84
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php84___lsphp .php .php8 .phtml
  AddHandler application/x-httpd-ea-php84 .php .php8 .phtml
</IfModule>
# END PERGABI PHP84

HDR
    cat "$file" >> "$tmp"
    mv "$tmp" "$file"
}

ensure_php84_handler "$ROOT/.htaccess"
ensure_php84_handler "$ROOT/public/.htaccess"

if [[ -d "$ROOT/.git" ]]; then
    cat > "$ROOT/.git/hooks/post-merge" << 'HOOK'
#!/usr/bin/env bash
exec "$(git rev-parse --show-toplevel)/bin/hosting-update.sh"
HOOK
    chmod +x "$ROOT/.git/hooks/post-merge"
fi

export COMPOSER_HOME="${COMPOSER_HOME:-$HOME/.composer}"

if [[ ! -f "$ROOT/composer.phar" ]]; then
    run_php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    run_php composer-setup.php --install-dir="$ROOT" --filename=composer.phar --quiet
    rm -f "$ROOT/composer-setup.php"
fi

COMPOSER_DISABLE_SCRIPTS=1 run_php "$ROOT/composer.phar" install --no-dev --optimize-autoloader --no-interaction
run_php artisan package:discover --ansi --no-interaction

chmod -R u+rwX,g+rwX "$ROOT/storage" "$ROOT/bootstrap/cache"

if [[ ! -f "$ROOT/.env" ]]; then
    echo "File .env belum ada. Isi dulu, lalu jalankan ulang skrip ini." >&2
    exit 1
fi

if ! grep -q '^APP_KEY=base64:' "$ROOT/.env"; then
    run_php artisan key:generate --force
fi

run_php artisan migrate --force
run_php artisan storage:link --force >/dev/null 2>&1 || run_php artisan storage:link || true
run_php artisan config:clear
run_php artisan route:clear
run_php artisan view:clear
run_php artisan config:cache
run_php artisan route:cache
run_php artisan view:cache

echo "hosting-update: selesai"
