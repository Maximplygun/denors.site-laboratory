#!/usr/bin/env bash
# Local Git Bash. Transfers diagnostics only; no public deployment or saved password.
set -euo pipefail
repo=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
host=ci11012@vh310.timeweb.ru
root=/home/c/ci11012/denors.site-laboratory.ru
options=(-4 -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes -i "$HOME/.ssh/denors_timeweb")
ssh "${options[@]}" "$host" "umask 077; mkdir -p '$root/.deploy/db-check'"
scp "${options[@]}" "$repo/scripts/maintenance/check-db.php" "$repo/scripts/maintenance/check-db-remote.sh" "$host:$root/.deploy/db-check/"
ssh -t "${options[@]}" "$host" "bash '$root/.deploy/db-check/check-db-remote.sh'"
