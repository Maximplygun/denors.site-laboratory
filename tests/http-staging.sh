#!/usr/bin/env bash
# Read-only Day 4 acceptance. Never target the production domain.
set -Eeuo pipefail
base=${1:-https://denors.site-laboratory.ru}
base=${base%/}
if [[ "$base" != https://denors.site-laboratory.ru && ! "$base" =~ ^http://127\.0\.0\.1:[0-9]+$ ]]; then
    echo 'Only the fixed staging domain or a local PHP server is allowed.' >&2; exit 1
fi
scratch=$(mktemp -d)
trap 'rm -f -- "$scratch/headers" "$scratch/body"; rmdir -- "$scratch"' EXIT
checks=0
check() {
    local method=$1 path=$2 expected=$3 status
    local options=()
    if [[ "$method" == HEAD ]]; then options+=(--head); else options+=(-X "$method"); fi
    status=$(curl -q --noproxy '*' --path-as-is --connect-timeout 10 --max-time 30 \
        -sS "${options[@]}" -D "$scratch/headers" -o "$scratch/body" \
        -w '%{http_code}' "$base$path")
    [[ "$status" == "$expected" ]] || {
        printf 'FAIL: %s %s expected %s, got %s\n' "$method" "$path" "$expected" "$status" >&2
        exit 1
    }
    grep -Eiq '^X-Robots-Tag:.*noindex' "$scratch/headers" || {
        echo "FAIL: noindex header missing for $method $path" >&2; exit 1
    }
    if grep -Eiq '^Location:' "$scratch/headers"; then
        echo "FAIL: unexpected redirect for $path" >&2; exit 1
    fi
    if [[ "$expected" == 405 ]]; then
        grep -Eiq '^Allow: GET, HEAD[[:space:]]*$' "$scratch/headers" || {
            echo "FAIL: Allow header missing for $path" >&2; exit 1
        }
    fi
    if [[ "$method" == GET && "$expected" == 404 ]]; then
        grep -q '<h1>' "$scratch/body" || {
            echo "FAIL: application 404 HTML missing for $path" >&2; exit 1
        }
    fi
    checks=$((checks + 1))
    printf 'PASS: %s %s -> %s + noindex\n' "$method" "$path" "$status"
}
check GET / 200
check GET '/?day4=acceptance' 200
check HEAD / 200
check POST / 405
check GET /denors-day4-missing/ 404
check HEAD /denors-day4-missing/ 404
check POST /denors-day4-missing/ 404
check GET /about_us/ 404
check GET /ABOUT_US/ 404
check GET /about_us 404
check GET /config/app.php 404
check GET /bootstrap/app.php 404
check GET /storage/logs/day4-probe 404
# Dotfile policy may return 403 at the web server or 404 in the application.
status=$(curl -q --noproxy '*' --connect-timeout 10 --max-time 30 -sS \
    -o /dev/null -w '%{http_code}' "$base/.env")
[[ "$status" == 403 || "$status" == 404 ]] || { echo "FAIL: /.env -> $status" >&2; exit 1; }
echo "PASS: $checks routing HTTP checks; /.env denied ($status)."
