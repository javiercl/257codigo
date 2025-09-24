FROM php:8.2-fpm

# Instalar dependencias del sistema necesarias para MySQL
RUN apt-get update && apt-get install -y \
    && rm -rf /var/lib/apt/lists/*

# Instalar solo las extensiones PHP necesarias para MySQL
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli

# Exponer puerto
EXPOSE 9000

# Comando por defecto
CMD ["php-fpm"]
