#!/usr/bin/env bash
# Run once in LOCAL Git Bash, then source ~/.bashrc.
set -euo pipefail
repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../.." && pwd)
profile="$HOME/.bashrc"
marker='# DENORS staging command'
if [[ -f "$profile" ]] && grep -Fxq "$marker" "$profile"; then
    echo 'denors is already registered in ~/.bashrc; run: source ~/.bashrc'
    exit 0
fi
{
    printf '\n%s\n' "$marker"
    printf 'denors() { bash %q "$@"; }\n' "$repo/scripts/deploy/staging.sh"
} >> "$profile"
echo 'Installed. Run: source ~/.bashrc'
echo 'Then: denors check'
