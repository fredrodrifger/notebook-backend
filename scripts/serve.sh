#!/usr/bin/env bash
# Boots the API locally: prepares the environment, migrates, serves and waits for /api/health.
set -euo pipefail

API_HOST="${API_HOST:-127.0.0.1}"
API_PORT="${API_PORT:-8000}"
HEALTH_URL="http://${API_HOST}:${API_PORT}/api/health"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

cd "$ROOT"

if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate --ansi
fi

[ -f database/database.sqlite ] || touch database/database.sqlite

php artisan migrate --force --ansi >/dev/null

php artisan serve --host="$API_HOST" --port="$API_PORT" &
api_pid=$!
trap 'kill "$api_pid" 2>/dev/null || true' EXIT INT TERM

for _ in $(seq 1 30); do
  if curl -fsS "$HEALTH_URL" >/dev/null 2>&1; then
    echo "API ready on ${HEALTH_URL}"
    wait "$api_pid"
    exit 0
  fi

  sleep 1
done

echo "API did not answer on ${HEALTH_URL} within 30s" >&2
exit 1
