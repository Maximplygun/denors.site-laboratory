#!/usr/bin/env bash
# Run in LOCAL Git Bash. Deploys committed code only, never runtime data.
set -euo pipefail
repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../.." && pwd)
cd "$repo"
host=ci11012@vh310.timeweb.ru
root=/home/c/ci11012/denors.site-laboratory.ru
key="$HOME/.ssh/denors_timeweb"
mode=${1:-deploy}
if [[ "$mode" != deploy && "$mode" != check ]]; then
    echo 'Usage: bash scripts/deploy/staging.sh [check|deploy]' >&2
    exit 1
fi
for command in git ssh scp tar sha256sum; do
    command -v "$command" >/dev/null || { echo "Missing: $command" >&2; exit 1; }
done
[[ -f "$key" ]] || { echo "SSH key missing: $key" >&2; exit 1; }
ssh_options=(-4 -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes -o ConnectTimeout=20 -i "$key")
ssh "${ssh_options[@]}" "$host" "test -d '$root' && test -x /opt/php83/bin/php && git --version && /opt/php83/bin/php -r 'echo PHP_VERSION, PHP_EOL;'"
# Run the isolated switch/rollback test on Linux before touching the public root.
tar -cf - scripts/deploy/activate-staging.sh tests/deploy.sh | ssh "${ssh_options[@]}" "$host" "umask 077; mkdir -p '$root/.deploy/selftest' && cd '$root/.deploy/selftest' && tar -xf - && bash tests/deploy.sh"
[[ "$mode" == deploy ]] || exit 0
[[ -z "$(git status --porcelain)" ]] || {
    echo 'Commit your changes first; the working tree must be clean.' >&2; exit 1;
}
branch=$(git symbolic-ref --short HEAD)
[[ "$branch" == develop || "$branch" == feature/* ]] || {
    echo 'Staging deployment is allowed from develop or feature/* only.' >&2; exit 1;
}
[[ "$(git remote get-url origin)" == https://github.com/Maximplygun/denors.site-laboratory.git ]] || {
    echo 'Unexpected origin; refusing deployment.' >&2; exit 1;
}
commit=$(git rev-parse HEAD)
git push -u origin "$branch"
remote_commit=$(git ls-remote origin "refs/heads/$branch" | cut -f1)
[[ "$remote_commit" == "$commit" ]] || { echo 'Remote commit mismatch.' >&2; exit 1; }
scratch=$(mktemp -d)
trap 'rm -f -- "$scratch/release.tar"; rmdir -- "$scratch"' EXIT
git archive --format=tar "$commit" > "$scratch/release.tar"
checksum=$(sha256sum "$scratch/release.tar" | cut -d' ' -f1)
id="$(date -u +%Y%m%dT%H%M%SZ)-${commit:0:12}"
ssh "${ssh_options[@]}" "$host" "umask 077; mkdir -p '$root/.deploy/incoming' '$root/releases'"
scp "${ssh_options[@]}" "$scratch/release.tar" "$host:$root/.deploy/incoming/$id.tar"
ssh "${ssh_options[@]}" "$host" "bash -s -- '$id' '$checksum' '$commit'" < scripts/deploy/activate-staging.sh
