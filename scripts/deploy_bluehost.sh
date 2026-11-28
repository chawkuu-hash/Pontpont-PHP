#!/usr/bin/env bash
set -e

# Bluehost 1-Click Deploy Script for Pont Pont Handbag Shop
HOST="50.6.52.211"
USER="fgkijemy"
PASS='}b3N{Al&p}gp;lv1'
REMOTE_PATH="public_html"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

echo "🚀 Starting deployment to Bluehost ($HOST)..."

export SSHPASS="$PASS"

cd "$PROJECT_ROOT"
sshpass -e scp -r -o StrictHostKeyChecking=no \
  index.php promotions.php admin.php profile.php install.php .htaccess README.md plan.md shop_api_explain.md "connect facebook developer app.md" \
  config api database assets \
  "$USER@$HOST:$REMOTE_PATH/"

echo "✅ Files uploaded successfully!"
echo "🔍 Checking live response..."
curl -sI "https://fgk.ije.mybluehost.me" | head -n 5
echo "🎉 Deployment completed successfully!"
