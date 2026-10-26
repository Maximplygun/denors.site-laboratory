#!/usr/bin/env bash
# Remote read-only audit. Do not print environment variables or account crontab.
set -euo pipefail
root=/home/c/ci11012/denors.site-laboratory.ru
[[ -d "$root" ]]
echo '=== CLI PHP ==='
/opt/php83/bin/php -r '
echo "PHP=", PHP_VERSION, " SAPI=", PHP_SAPI, PHP_EOL;
foreach (["memory_limit", "max_execution_time", "max_input_time", "upload_max_filesize", "post_max_size", "max_file_uploads", "opcache.enable", "opcache.enable_cli"] as $key) {
    echo $key, "=", ini_get($key), PHP_EOL;
}
echo "image_webp=", function_exists("imagewebp") ? "yes" : "no", PHP_EOL;
echo "image_avif=", function_exists("imageavif") ? "yes" : "no", PHP_EOL;
'
echo '=== Tools ==='
for tool in git composer crontab flock curl tar sha256sum; do
    if command -v "$tool" >/dev/null 2>&1; then
        printf '%s=available\n' "$tool"
    else
        printf '%s=unavailable\n' "$tool"
    fi
done
echo '=== Filesystem (not account quota) ==='
df -h "$root" | tail -n 1
df -i "$root" | tail -n 1
echo '=== Shell process limits (not tariff guarantees) ==='
printf 'open_files='; ulimit -n
printf 'processes='; ulimit -u
echo '=== Public root ==='
if [[ -L "$root/public_html" ]]; then
    readlink -f "$root/public_html"
else
    echo 'public_html is not a symlink'
fi
echo '=== Backup ==='
echo 'Retention, account quotas and restore procedure: verify in Timeweb panel.'
