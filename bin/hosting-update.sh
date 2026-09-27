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
  AddHandler application/x-httpd-alt-php84___lsphp .php .php8 .phtml
  AddHandler application/x-httpd-alt-php84 .php .php8 .phtml
  AddHandler application/x-httpd-ea-php84___lsphp .php .php8 .phtml
</IfModule>
# END PERGABI PHP84

HDR
    cat "$file" >> "$tmp"
    mv "$tmp" "$file"
}

install_php84_cgi() {
    cat > "$ROOT/public/index.cgi" << 'CGI'
#!/bin/sh
export REDIRECT_STATUS=200
PUBLIC="$(CDPATH= cd -- "$(dirname "$0")" && pwd)"
export SCRIPT_FILENAME="$PUBLIC/cgi-front.php"
cd "$PUBLIC" || exit 1
exec /opt/alt/php84/usr/bin/php-cgi -d cgi.force_redirect=0 -d disable_functions=
CGI
    chmod 755 "$ROOT/public/index.cgi"
}

restore_laravel_index() {
    local index="$ROOT/public/index.php"
    local backup="$ROOT/public/index.laravel.php"
    if [[ -f "$backup" ]] && ! grep -q 'LARAVEL_START' "$index" 2>/dev/null; then
        cp "$backup" "$index"
    fi
}

ensure_public_html_front() {
    local html
    html="$(cd "$ROOT/.." && pwd)"
    [[ "$(basename "$html")" == "public_html" ]] || return 0

    local folder
    folder="$(basename "$ROOT")"
    local front="$html/.htaccess"
    local marker='# BEGIN PERGABI FRONT'

    local ini
    ini="$(awk '/BEGIN cPanel-generated php ini/,/END cPanel-generated php ini directives/' "$front" 2>/dev/null || true)"

    cat > "$front" << EOF
Options -Indexes

${marker}
<IfModule mime_module>
  AddHandler application/x-httpd-alt-php84___lsphp .php .php8 .phtml
  AddHandler application/x-httpd-alt-php84 .php .php8 .phtml
  AddHandler application/x-httpd-ea-php84___lsphp .php .php8 .phtml
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteRule ^\\.well-known/ - [L]

    RewriteRule ^${folder}/public/ - [L]
    RewriteRule ^${folder}/ - [F]

    RewriteCond %{DOCUMENT_ROOT}/${folder}/public/\$1 -f
    RewriteRule ^(.*)\$ ${folder}/public/\$1 [L]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ ${folder}/public/index.php [L]
</IfModule>
# END PERGABI FRONT

EOF
    if [[ -n "$ini" ]]; then
        printf '%s\n' "$ini" >> "$front"
    fi

    ln -sfn "${folder}/public/index.php" "$html/index.php"
}

ensure_php84_handler "$ROOT/.htaccess"
ensure_php84_handler "$ROOT/public/.htaccess"
install_php84_cgi
restore_laravel_index
ensure_public_html_front

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
