#!/usr/bin/env bash
# Remote interactive read-only test for the dedicated staging database.
set -euo pipefail
export DENORS_DB_HOST=localhost
export DENORS_DB_NAME=ci11012_denorstg
export DENORS_DB_USER=ci11012_denorstg
echo "Database: $DENORS_DB_NAME; user: $DENORS_DB_USER; host: $DENORS_DB_HOST"
echo 'Enter the password created for THIS database, not the Timeweb account password.'
printf 'Database password: ' > /dev/tty
IFS= read -r -s DENORS_DB_PASSWORD < /dev/tty
printf '\n' > /dev/tty
export DENORS_DB_PASSWORD
trap 'unset DENORS_DB_PASSWORD' EXIT
/opt/php83/bin/php "$(dirname "${BASH_SOURCE[0]}")/check-db.php"
