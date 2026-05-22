#!/bin/bash
set -e

echo "Limpiando caché de configuración..."
php artisan config:clear
php artisan cache:clear

echo "Ejecutando migraciones..."
php artisan migrate --force

echo "Iniciando Apache..."
apache2-foreground