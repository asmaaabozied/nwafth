#!/usr/bin/env bash
#
# run.sh - build and start the Laravel API + MySQL stack with Docker.
#
#   ./run.sh          build images, start containers, install deps, migrate, seed
#   ./run.sh stop     stop the containers (keeps the database volume)
#   ./run.sh down     stop and remove containers + network (keeps the volume)
#   ./run.sh destroy  stop and remove everything, including the database volume
#   ./run.sh fresh    rebuild from scratch and re-run migrations (drops data)
#   ./run.sh seed     re-run database seeders
#   ./run.sh artisan  ...   run an artisan command inside the app container
#   ./run.sh composer ...   run a composer command inside the app container
#   ./run.sh test     run the PHPUnit test suite
#   ./run.sh shell    open a shell in the app container
#   ./run.sh logs     tail the container logs
#
set -euo pipefail

cd "$(dirname "$0")"


# --- Configuration --------------------------------------------------------
APP_SERVICE="app"
DB_CONTAINER="nwafth_mysql"
DC="docker compose"

# Colours (fall back to plain text when not a TTY)
if [ -t 1 ]; then
  BLUE=$'\033[1;34m'; GREEN=$'\033[1;32m'; YELLOW=$'\033[1;33m'; RED=$'\033[1;31m'; NC=$'\033[0m'
else
  BLUE=""; GREEN=""; YELLOW=""; RED=""; NC=""
fi
info()  { echo "${BLUE}==>${NC} $*"; }
ok()    { echo "${GREEN}✓${NC} $*"; }
warn()  { echo "${YELLOW}!${NC} $*"; }
die()   { echo "${RED}✗ $*${NC}" >&2; exit 1; }

# --- Pre-flight ---------------------------------------------------------
command -v docker >/dev/null 2>&1 || die "Docker is not installed or not on PATH."
docker info >/dev/null 2>&1 || die "Docker daemon is not running. Start Docker Desktop and retry."

APP_PORT="$(grep -E '^APP_PORT=' .env 2>/dev/null | head -1 | cut -d= -f2 || true)"
APP_PORT="${APP_PORT:-8000}"

# --- Sub-commands -----------------------------------------------------
case "${1:-up}" in
  stop)
    info "Stopping containers..."
    $DC stop
    ok "Stopped. Run ./run.sh to start again."
    exit 0
    ;;
  down)
    info "Removing containers and network (database volume kept)..."
    $DC down
    ok "Done."
    exit 0
    ;;
  destroy)
    warn "This deletes the database volume. Data will be lost."
    $DC down -v
    ok "Everything removed."
    exit 0
    ;;
  fresh)
    warn "Rebuilding from scratch - database will be wiped."
    $DC down -v
    ;;
  seed)
    exec $DC exec "$APP_SERVICE" php artisan db:seed
    ;;
  artisan)
    shift
    exec $DC exec "$APP_SERVICE" php artisan "$@"
    ;;
  composer)
    shift
    exec $DC exec "$APP_SERVICE" composer "$@"
    ;;
  test)
    shift
    exec $DC exec "$APP_SERVICE" php artisan test "$@"
    ;;
  shell|bash|sh)
    exec $DC exec "$APP_SERVICE" bash
    ;;
  logs)
    exec $DC logs -f
    ;;
  up|"")
    ;;
  *)
    die "Unknown command '${1}'. Run without arguments for the usage summary at the top of this file."
    ;;
esac

# --- 1. Environment file ---------------------------------------------
if [ ! -f .env ]; then
  cp .env.example .env
  ok "Created .env from .env.example"
fi

# --- 2. Build images and start containers ---------------------------
info "Building images and starting containers..."
$DC up -d --build

# --- 3. Wait for MySQL to accept connections -----------------------
info "Waiting for MySQL to become healthy..."
for i in $(seq 1 60); do
  status="$(docker inspect -f '{{.State.Health.Status}}' "$DB_CONTAINER" 2>/dev/null || echo starting)"
  if [ "$status" = "healthy" ]; then
    ok "MySQL is ready."
    break
  fi
  if [ "$i" -eq 60 ]; then
    die "MySQL did not become healthy in time. Check: ./run.sh logs"
  fi
  sleep 2
done

# --- 4. PHP dependencies ------------------------------------------
info "Installing PHP dependencies..."
$DC exec -T "$APP_SERVICE" composer install --no-interaction --prefer-dist

# --- 5. Application key ------------------------------------------
if ! grep -qE '^APP_KEY=base64:.+' .env; then
  info "Generating application key..."
  $DC exec -T "$APP_SERVICE" php artisan key:generate --force
fi

# --- 6. Database migrations + seeders --------------------------
info "Running migrations..."
$DC exec -T "$APP_SERVICE" php artisan migrate --force

info "Seeding database..."
$DC exec -T "$APP_SERVICE" php artisan db:seed --force

# --- 7. Link public storage --------------------------------------------
info "Linking public storage..."
$DC exec -T "$APP_SERVICE" php artisan storage:link --force

# --- 8. Optimise --------------------------------------------
$DC exec -T "$APP_SERVICE" php artisan optimize:clear >/dev/null 2>&1 || true

DB_PORT="$(grep -E '^DB_FORWARD_PORT=' .env | head -1 | cut -d= -f2 || echo 3306)"
DB_NAME="$(grep -E '^DB_DATABASE=' .env | head -1 | cut -d= -f2 || echo nwafth)"

echo
ok "API stack is up."
echo "   Root:         ${GREEN}http://localhost:${APP_PORT}${NC}"
echo "   Website API:  ${GREEN}http://localhost:${APP_PORT}/api/website/v1${NC}"
echo "   Admin API:    ${GREEN}http://localhost:${APP_PORT}/api/admin/v1${NC}"
echo "   MySQL:        127.0.0.1:${DB_PORT}  (database: ${DB_NAME})"
echo
echo "   Default admin login:  admin@example.com / password"
echo "   Demo website user:    user@example.com / password"
echo
echo "   ./run.sh logs      tail logs"
echo "   ./run.sh artisan   run artisan commands"
echo "   ./run.sh test      run the test suite"
echo "   ./run.sh stop      stop the stack"
