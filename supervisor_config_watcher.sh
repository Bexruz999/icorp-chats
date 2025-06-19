#!/bin/bash
WATCH_DIR="/var/www/icorp-chats/storage/app"
SUPERVISOR_DIR="/etc/supervisor/conf.d"

while true; do
  for file in "$WATCH_DIR"/supervisor_*.conf; do
    [ -e "$file" ] || continue
    mv "$file" "$SUPERVISOR_DIR"/
    supervisorctl reread
    supervisorctl update
  done
  sleep 5
done
