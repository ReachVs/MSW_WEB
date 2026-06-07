#!/usr/bin/env sh
set -e

LOCKFILE="package-lock.json"
STAMP="node_modules/.package-lock.sha256"

if [ -f "$LOCKFILE" ]; then
  CURRENT_HASH=$(sha256sum "$LOCKFILE" | awk '{print $1}')
  if [ ! -f "$STAMP" ] || [ "$(cat "$STAMP")" != "$CURRENT_HASH" ]; then
    npm ci
    echo "$CURRENT_HASH" > "$STAMP"
  fi
fi

exec "$@"
