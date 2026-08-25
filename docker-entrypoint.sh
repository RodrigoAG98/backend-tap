#!/bin/sh

# Asegurar que Apache (www-data) sea dueño de las carpetas de almacenamiento y logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Esperar unos segundos o ejecutar comandos de inicialización
echo "Ejecutando optimizaciones de Laravel..."
php artisan config:clear
php artisan cache:clear

echo "Generando documentación de Swagger..."
php artisan l5-swagger:generate

echo "Ejecutando migraciones y seeders..."
php artisan migrate --force
php artisan db:seed --force

echo "Iniciando Apache..."
exec apache2-foreground