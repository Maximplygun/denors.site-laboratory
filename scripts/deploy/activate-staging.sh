#!/usr/bin/env bash
# Invoked by staging.sh via stdin on Timeweb. No production paths allowed.
set -Eeuo pipefail
trap 'code=$?; printf "Activation failed at line %s (exit %s).\n" "$LINENO" "$code" >&2' ERR
umask 077
root=/home/c/ci11012/denors.site-laboratory.ru
url=https://denors.site-laboratory.ru
php=/opt/php83/bin/php
id=${1:?Release ID required}
checksum=${2:?Archive checksum required}
commit=${3:?Commit required}
[[ "$id" =~ ^[0-9]{8}T[0-9]{6}Z-[0-9a-f]{12}$ ]]
[[ "$checksum" =~ ^[0-9a-f]{64}$ && "$commit" =~ ^[0-9a-f]{40}$ ]]
[[ -d "$root" && ! -L "$root" && -x "$php" ]]
[[ "$(realpath "$root")" == "$root" ]]
[[ ! -L "$root/releases" && ! -L "$root/.deploy" ]]
command -v curl >/dev/null
command -v sha256sum >/dev/null
command -v flock >/dev/null
exec 9>"$root/.deploy/lock"
flock -n 9 || { echo 'Another deployment is running.' >&2; exit 1; }
archive="$root/.deploy/incoming/$id.tar"
release="$root/releases/$id"
[[ -f "$archive" && ! -e "$release" ]]
printf '%s  %s\n' "$checksum" "$archive" | sha256sum -c -
"$php" -r 'exit(version_compare(PHP_VERSION,"8.3.0",">=") ? 0 : 1);'
# Only archives made from the repository are accepted; exclude private runtime.
if tar -tf "$archive" | grep -vx '\.env\.example' | grep -E '(^/|(^|/)\.\.(/|$)|^\.env($|\.)|^storage/|^public/uploads/|^public_html/)'; then
    echo 'Archive contains prohibited paths.' >&2; exit 1
fi
mkdir "$release"
tar -xf "$archive" -C "$release" --no-same-owner
[[ -f "$release/public/index.php" && -f "$release/public/robots.txt" ]]
[[ ! -f "$release/composer.json" ]] || { echo 'Dependency installation is not configured yet.' >&2; exit 1; }
if [[ -d "$release/database/migrations" ]] && find "$release/database/migrations" -type f \( -name '*.php' -o -name '*.sql' \) -print -quit | grep -q .; then
    echo 'A migration runner must be configured before deploying database changes.' >&2; exit 1
fi
while IFS= read -r -d '' file; do "$php" -l "$file" >/dev/null; done < <(find "$release" -type f -name '*.php' -print0)
if [[ -f "$release/tests/application.php" ]]; then
    "$php" "$release/tests/application.php"
fi
if [[ -f "$release/tests/routing.php" ]]; then
    "$php" "$release/tests/routing.php"
fi
[[ -f "$release/public/.htaccess" ]]
# Owner postponed Basic Auth for this technical domain on 2026-10-29.
# Keep HTTPS, private runtime and noindex; do not consume old smoke credentials.
# Initial setup accepts only the known empty root and preserves cgi-bin settings.
web="$root/public_html"
initial=false
if [[ -L "$web" ]]; then
    old=$(readlink -f "$web")
    [[ "$old" == "$root/releases/"*/public && -d "$old" ]]
else
    [[ -d "$web" ]]
    unknown=$(find "$web" -mindepth 1 -maxdepth 1 ! -name assets ! -name cgi-bin ! -name denors-root-check.txt -print -quit)
    [[ -z "$unknown" ]] || { echo "Unexpected public root entry: $unknown" >&2; exit 1; }
    if [[ -d "$web/assets" ]] && find "$web/assets" -type f -print -quit | grep -q .; then
        echo 'Existing public assets require a reviewed migration.' >&2; exit 1
    fi
    initial=true
    old="$root/.deploy/original-public-$id"
fi
mkdir -p "$root/storage/logs" "$root/storage/cache" "$root/storage/sessions" "$root/shared/uploads"
[[ ! -e "$release/storage" && ! -e "$release/public/uploads" ]]
ln -s "$root/storage" "$release/storage"
ln -s "$root/shared/uploads" "$release/public/uploads"
if [[ -f "$root/.env" ]]; then ln -s "$root/.env" "$release/.env"; fi
cgi="$web/cgi-bin"
if [[ "$initial" == true ]]; then cgi="$old/cgi-bin"; fi
if [[ -d "$web/cgi-bin" ]]; then ln -s "$(realpath -m "$cgi")" "$release/public/cgi-bin"; fi
printf '%s\n' "$commit" > "$release/public/denors-release.txt"
next="$root/.deploy/next-$id"
ln -s "$release/public" "$next"
switched=false
restore() {
    result=$?
    trap - EXIT
    if [[ "$result" != 0 && "$switched" == true ]]; then
        restore_link="$root/.deploy/restore-$id"
        ln -s "$old" "$restore_link"
        mv -Tf "$restore_link" "$web"
        echo 'Smoke test failed: previous public root restored.' >&2
    fi
    exit "$result"
}
trap restore EXIT
if [[ "$initial" == true ]]; then
    mv "$web" "$old"
    # Restore the original directory if the first switch cannot be completed.
    if ! mv -T "$next" "$web"; then mv "$old" "$web"; exit 1; fi
else
    mv -Tf "$next" "$web"
fi
switched=true
# Ignore user curl defaults and require valid TLS without credentials.
actual=$(curl -q -4 --noproxy '*' --connect-timeout 10 --max-time 30 -fsS "$url/denors-release.txt?release=$id")
[[ "$actual" == "$commit" ]]
robots=$(curl -q -4 --noproxy '*' --connect-timeout 10 --max-time 30 -fsS "$url/robots.txt?release=$id")
printf '%s\n' "$robots" | grep -Eq '^Disallow: /[[:space:]]*$'
headers="$root/.deploy/headers-$id"
status=$(curl -q -4 --noproxy '*' --connect-timeout 10 --max-time 30 -sS -D "$headers" -o /dev/null -w '%{http_code}' "$url/?release=$id")
# The staging placeholder is reachable for hosting and certificate preflight.
[[ "$status" == 200 ]]
grep -Eiq '^X-Robots-Tag:.*noindex' "$headers"
previous_next="$root/.deploy/previous-next-$id"
ln -s "$old" "$previous_next"
mv -Tf "$previous_next" "$root/.deploy/previous-public"
printf '%s\n' "$commit" > "$root/.deploy/deployed-commit"
switched=false
rm -f -- "$archive" "$headers"
echo "Staging deployed: $id ($commit). HTTPS 200 + noindex verified."
