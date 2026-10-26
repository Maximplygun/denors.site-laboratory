#!/usr/bin/env bash
# Local Git Bash: sends the setup program, never credentials, to Timeweb.
set -euo pipefail
repo=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
host=ci11012@vh310.timeweb.ru
root=/home/c/ci11012/denors.site-laboratory.ru
options=(-4 -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes -i "$HOME/.ssh/denors_timeweb")
ssh "${options[@]}" "$host" "umask 077; mkdir -p '$root/.deploy'"
scp "${options[@]}" "$repo/scripts/deploy/setup-access-remote.sh" "$host:$root/.deploy/setup-access.sh"
ssh -t "${options[@]}" "$host" "bash '$root/.deploy/setup-access.sh'"
