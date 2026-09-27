#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PHP="${PERGABI_PHP:-/opt/cpanel/ea-php84/root/usr/bin/php}"

if [[ ! -x "$PHP" ]]; then
    echo "PHP 8.4 tidak ditemukan: $PHP" >&2
    exit 1
fi

ensure_php83_handler() {
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

ensure_php83_handler "$ROOT/.htaccess"
ensure_php83_handler "$ROOT/public/.htaccess"

if [[ -d "$ROOT/.git" ]]; then
    cat > "$ROOT/.git/hooks/post-merge" << 'HOOK'
#!/usr/bin/env bash
exec "$(git rev-parse --show-toplevel)/bin/hosting-update.sh"
HOOK
    chmod +x "$ROOT/.git/hooks/post-merge"
fi

export COMPOSER_HOME="${COMPOSER_HOME:-$HOME/.composer}"

if [[ ! -f "$ROOT/composer.phar" ]]; then
    "$PHP" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    "$PHP" composer-setup.php --install-dir="$ROOT" --filename=composer.phar --quiet
    rm -f "$ROOT/composer-setup.php"
fi

"$PHP" "$ROOT/composer.phar" install --no-dev --optimize-autoloader --no-interaction

chmod -R u+rwX,g+rwX "$ROOT/storage" "$ROOT/bootstrap/cache"

if [[ ! -f "$ROOT/.env" ]]; then
    echo "File .env belum ada. Isi dulu, lalu jalankan ulang skrip ini." >&2
    exit 1
fi

if ! grep -q '^APP_KEY=base64:' "$ROOT/.env"; then
    "$PHP" artisan key:generate --force
fi

"$PHP" artisan migrate --force
"$PHP" artisan storage:link --force >/dev/null 2>&1 || "$PHP" artisan storage:link || true
"$PHP" artisan config:clear
"$PHP" artisan route:clear
"$PHP" artisan view:clear
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

echo "hosting-update: selesai"
