#!/usr/bin/env bash
# Watchdog for the live service: starts a listener for every configured port that is not answering.
# Silent (no output) when everything is healthy — safe to run from the scheduler every few minutes.
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
EXTRA_PORTS="${NOTEBOOK_EXTRA_PORTS:-}"

mkdir -p storage/logs

started=""

start_listener() {
  local port="$1"
  local health="http://127.0.0.1:${port}/api/health"

  if curl -fsS --max-time 3 "$health" >/dev/null 2>&1; then
    return 0
  fi

  setsid php artisan serve --host="$HOST" --port="$port" >>"storage/logs/live-server-${port}.log" 2>&1 </dev/null &

  for _ in $(seq 1 15); do
    if curl -fsS --max-time 2 "$health" >/dev/null 2>&1; then
      started="${started} ${port}"
      return 0
    fi

    sleep 1
  done

  echo "notebook: port ${port} did not answer after a restart attempt ($(date -u '+%Y-%m-%d %H:%M:%S UTC'))"
  return 1
}

failed=0
start_listener "$PORT" || failed=1

for port in $EXTRA_PORTS; do
  start_listener "$port" || failed=1
done

if [ -n "$started" ]; then
  echo "notebook live service (re)started on ${HOST}:${started# } at $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
fi

exit "$failed"
