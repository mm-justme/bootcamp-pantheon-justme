#!/usr/bin/env sh
# Якщо це збірка на Pantheon – просто вийти (не запускати перевірки).
if [ -n "$PANTHEON_ENVIRONMENT" ] || [ -n "$TERMINUS_SITE" ]; then
  exit 0
fi

# Локально: якщо є ddev – використовуємо його PHP, інакше – системний php.
if command -v ddev >/dev/null 2>&1; then
  ddev exec php "$@"
else
  php "$@"
fi
