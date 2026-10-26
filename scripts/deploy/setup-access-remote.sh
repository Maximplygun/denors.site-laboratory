#!/usr/bin/env bash
# Interactive remote setup. Do not run with bash -x: passwords must stay private.
set -euo pipefail
umask 077
root=/home/c/ci11012/denors.site-laboratory.ru
[[ -d "$root" && "$(realpath "$root")" == "$root" ]]
[[ ! -L "$root/shared" && ! -L "$root/.deploy" ]]
command -v htpasswd >/dev/null
command -v flock >/dev/null
mkdir -p "$root/shared" "$root/.deploy"
exec 9>"$root/.deploy/lock"
flock -n 9 || { echo 'Another setup or deployment is running.' >&2; exit 1; }
password_file="$root/shared/staging.htpasswd"
curl_file="$root/.deploy/smoke.curl"
if [[ -e "$password_file" || -e "$curl_file" ]]; then
    echo 'Access files already exist. Refusing to overwrite credentials.' >&2
    exit 1
fi
scratch=$(mktemp -d "$root/.deploy/access.XXXXXXXX")
trap 'unset password token; rm -f -- "$scratch/htpasswd" "$scratch/smoke.curl"; rmdir -- "$scratch"' EXIT
echo 'Staging browser login: denors'
echo 'Create a NEW staging password (not your Timeweb or SSH password).'
htpasswd -B -c "$scratch/htpasswd" denors
printf 'Repeat the SAME staging password for automatic HTTPS checks: ' > /dev/tty
IFS= read -r -s password < /dev/tty
printf '\n' > /dev/tty
[[ -n "$password" ]]
# -i reads the password from stdin; it never appears in process arguments.
printf '%s\n' "$password" | htpasswd -vi "$scratch/htpasswd" denors
token=$(printf 'denors:%s' "$password" | base64 | tr -d '\r\n')
unset password
printf 'header = "Authorization: Basic %s"\n' "$token" > "$scratch/smoke.curl"
unset token
chmod 600 "$scratch/htpasswd" "$scratch/smoke.curl"
mv "$scratch/htpasswd" "$password_file"
mv "$scratch/smoke.curl" "$curl_file"
echo 'Access files created outside web root. Run denors locally to activate protection.'
