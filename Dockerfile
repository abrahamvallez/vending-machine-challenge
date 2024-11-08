FROM php:8.2-cli

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
  git \
  unzip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /app

# Copiar archivos de configuración
COPY composer.json composer.lock ./

# Instalar dependencias
RUN composer install --no-scripts --no-autoloader

# Copiar el resto de la aplicación
COPY . .

# Generar autoloader
RUN composer dump-autoload
