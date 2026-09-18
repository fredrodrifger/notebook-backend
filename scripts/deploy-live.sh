#!/usr/bin/env bash
# Publishes the built UI next to the API and serves both on one origin (default 0.0.0.0:5353).
#
# Production settings live in .env.live (gitignored): NOTEBOOK_HOST/NOTEBOOK_PORT, APP_ENV,
# APP_DEBUG, NOTEBOOK_API_TOKEN. Environment variables win over .env inside Laravel, so the local
# development .env keeps its open, debug-friendly values.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [ ! -f .env.live ]; then
  echo "No .env.live found — create it with:" >&2
  echo "  NOTEBOOK_HOST=0.0.0.0" >&2
  echo "  NOTEBOOK_PORT=5353" >&2
  echo "  APP_ENV=production" >&2
  echo "  APP_DEBUG=false" >&2
  echo "  NOTEBOOK_API_TOKEN=\$(openssl rand -hex 24)" >&2
  exit 1
fi

set -a
# shellcheck disable=SC1091
. ./.env.live
set +a

HOST="${NOTEBOOK_HOST:-0.0.0.0}"
PORT="${NOTEBOOK_PORT:-5353}"
FRONTEND_DIR="${FRONTEND_DIR:-$(cd "$ROOT/.." && pwd)/notebook-frontend}"
PUBLIC_DIR="$ROOT/public"

if [ -z "${NOTEBOOK_API_TOKEN:-}" ]; then
  echo "NOTEBOOK_API_TOKEN is empty — refusing to expose the API without a token." >&2
  exit 1
fi

if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate --ansi
fi

[ -f database/database.sqlite ] || touch database/database.sqlite

if [ ! -d "$FRONTEND_DIR" ]; then
  echo "No frontend checkout at ${FRONTEND_DIR} — set FRONTEND_DIR=..." >&2
  exit 1
fi

echo "== build and publish the UI =="
(cd "$FRONTEND_DIR" && npm run build)
rm -rf "$PUBLIC_DIR/assets"
cp -R "$FRONTEND_DIR/dist/assets" "$PUBLIC_DIR/assets"
cp "$FRONTEND_DIR/dist/index.html" "$PUBLIC_DIR/index.html"
find "$FRONTEND_DIR/dist" -maxdepth 1 -type f ! -name 'index.html' -exec cp {} "$PUBLIC_DIR/" \;

echo "== migrate =="
php artisan migrate --force --ansi >/dev/null

echo "== serve on ${HOST}:${PORT} (APP_ENV=${APP_ENV}) =="
echo "Notebook: http://${HOST}:${PORT}/fa/notes   API under /api, gated by the access token"
exec php artisan serve --host="$HOST" --port="$PORT"
