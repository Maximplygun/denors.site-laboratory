#!/usr/bin/env bash
# Local Git Bash. Read-only, compact host diagnostics.
set -euo pipefail
repo=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
ssh -4 -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes \
    -i "$HOME/.ssh/denors_timeweb" ci11012@vh310.timeweb.ru \
    'bash -s' < "$repo/scripts/maintenance/check-hosting-remote.sh"
