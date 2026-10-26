#!/usr/bin/env bash
# Local integration test: isolated filesystem and fake HTTP/lock/PHP tools.
set -Eeuo pipefail
trap 'code=$?; printf "Self-test failed at line %s (exit %s).\n" "$LINENO" "$code" >&2' ERR
echo 'Starting isolated release test...'
case "$(uname -s)" in
    Linux) ;;
    *) echo 'SKIP: this integration test requires Linux symlink semantics.'; exit 77 ;;
esac
repo=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
test_parent=$(realpath "${TMPDIR:-/tmp}")
scratch=$(mktemp -d "$test_parent/denors-deploy-test.XXXXXXXX")
scratch=$(realpath "$scratch")
[[ "$(dirname "$scratch")" == "$test_parent" && "$(basename "$scratch")" == denors-deploy-test.* ]]
trap 'rm -rf -- "$scratch"' EXIT
mkdir -p "$scratch/source/public"
# Export a shell function: /tmp may be mounted noexec on shared hosting.
# PHP and flock use the actual host binaries; only HTTP is simulated.
curl() {
    [[ "$*" == *https://denors.site-laboratory.ru/* ]] || {
        echo 'Expected HTTPS smoke URL.' >&2; return 1;
    }
if [[ "$*" == *denors-release.txt* ]]; then
    [[ "${FAIL_SMOKE:-0}" != 1 ]] || exit 22
    cat "$TEST_ROOT/public_html/denors-release.txt"
else
    while [[ $# -gt 0 ]]; do
        if [[ "$1" == -D ]]; then shift; printf 'X-Robots-Tag: noindex\r\n' > "$1"; fi
        shift
    done
    printf 200
fi
}
export -f curl
export TEST_ROOT="$scratch/site"
mkdir -p "$TEST_ROOT/public_html/assets/css" "$TEST_ROOT/public_html/cgi-bin" "$TEST_ROOT/.deploy/incoming" "$TEST_ROOT/releases"
printf 'original-settings' > "$TEST_ROOT/public_html/cgi-bin/settings.ini"
printf 'private-setting' > "$TEST_ROOT/.env"
printf '<?php echo "placeholder";' > "$scratch/source/public/index.php"
printf 'User-agent: *\nDisallow: /\n' > "$scratch/source/public/robots.txt"
sed -e "s|^root=.*|root=$TEST_ROOT|" "$repo/scripts/deploy/activate-staging.sh" > "$scratch/activate.sh"
commit=1111111111111111111111111111111111111111
make_archive() {
    tar -cf "$TEST_ROOT/.deploy/incoming/$1.tar" -C "$scratch/source" public
    checksum=$(sha256sum "$TEST_ROOT/.deploy/incoming/$1.tar" | cut -d' ' -f1)
}
id=20261026T200000Z-111111111111
make_archive "$id"
bash "$scratch/activate.sh" "$id" "$checksum" "$commit"
[[ -L "$TEST_ROOT/public_html" ]]
[[ "$(cat "$TEST_ROOT/public_html/cgi-bin/settings.ini")" == original-settings ]]
[[ "$(cat "$TEST_ROOT/releases/$id/.env")" == private-setting ]]
first=$(readlink -f "$TEST_ROOT/public_html")
id=20261026T200001Z-111111111111
make_archive "$id"
if FAIL_SMOKE=1 bash "$scratch/activate.sh" "$id" "$checksum" "$commit"; then
    echo 'Expected HTTP failure' >&2; exit 1
fi
[[ "$(readlink -f "$TEST_ROOT/public_html")" == "$first" ]]
id=20261026T200002Z-111111111111
make_archive "$id"
if bash "$scratch/activate.sh" "$id" "$(printf '%064d' 0)" "$commit"; then
    echo 'Expected checksum failure' >&2; exit 1
fi
[[ "$(readlink -f "$TEST_ROOT/public_html")" == "$first" ]]
echo 'PASS: initial switch, retained CGI/env, HTTP failure rollback, checksum refusal.'
