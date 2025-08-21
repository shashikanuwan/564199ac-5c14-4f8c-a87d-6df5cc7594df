#!/bin/sh
set -e

# Copy .env if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env file from .env.example"
fi

# Install composer dependencies if vendor folder is missing
if [ ! -d vendor ]; then
    composer install --no-interaction --optimize-autoloader
    echo "Installed Composer dependencies"
fi

# Generate application key if it doesn't exist
if ! grep -q '^APP_KEY=.*[A-Za-z0-9]' .env; then
    php artisan key:generate
    echo "Generated application key"
fi

# Install npm dependencies if node_modules folder is missing
if [ ! -d node_modules ]; then
    npm install
    echo "Installed npm dependencies"
fi

# Run the command passed to the container
exec "$@"
