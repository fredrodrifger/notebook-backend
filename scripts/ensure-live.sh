#!/usr/bin/env bash
# Watchdog for the live service: starts it when it is not answering, stays silent when healthy.
# Used both manually and by the scheduler (empty output = nothing to report).
set -euo pipefail

ROOT="${NOTEBOOK_ROOT:-/data/notebook-backend}"
cd "$ROOT"

if [ ! -f .env.live ]; then
  echo "notebook watchdog: .env.live is missing in ${ROOT} — cannot start the live service"
  exit 1
fi

set -a
# shellcheck disable=SC1091
. ./.env.live
set +a

HOST="${NOTEBOOK_HOST:-0.0.0.0}"
PORT="${NOTEBOOK_PORT:-5353}"
HEALTH="http://127.0.0.1:${PORT}/api/health"

if curl -fsS --max-time 3 "$HEALTH" >/dev/null 2>&1; then
  exit 0
fi

mkdir -p storage/logs

setsid php artisan serve --host="$HOST" --port="$PORT" >>storage/logs/live-server.log 2>&1 </dev/null &

for _ in $(seq 1 15); do
  if curl -fsS --max-time 2 "$HEALTH" >/dev/null 2>&1; then
    echo "notebook live service (re)started on ${HOST}:${PORT} at $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
    exit 0
  fi

  sleep 1
done

echo "notebook live service did NOT answer on ${HOST}:${PORT} after a restart attempt"
exit 1
