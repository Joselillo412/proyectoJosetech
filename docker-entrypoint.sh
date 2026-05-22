#!/bin/bash
set -e

echo "Limpiando caché de configuración..."
php artisan config:clear

echo "Ejecutando migraciones..."
php artisan migrate --force

echo "Limpiando caché de aplicación..."
php artisan cache:clear

echo "Iniciando Apache..."
apache2-foreground