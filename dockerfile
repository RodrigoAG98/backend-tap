FROM php:8.2-apache

# Instalar dependencias del sistema requeridas (incluyendo soporte para GD, ZIP y MongoDB)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    libzip-dev \
    pkg-config \
    zip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

# Habilitar el módulo mod_rewrite de Apache para Laravel
RUN a2enmod rewrite

# Cambiar el DocumentRoot de Apache a la carpeta /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf

# Instalar Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código de la aplicación al contenedor
COPY . .

# Instalar las dependencias de PHP sin optimizaciones de dev
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Asignar permisos correctos a las carpetas de almacenamiento y caché
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Dar permisos de ejecución al script de entrada
RUN chmod +x /var/www/html/docker-entrypoint.sh

# Exponer el puerto por defecto de Apache
EXPOSE 80

# Usar el script de entrada para ejecutar las tareas iniciales y luego Apache
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]