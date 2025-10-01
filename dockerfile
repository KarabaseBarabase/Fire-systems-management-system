# Базовый образ PHP
FROM php:8.2-fpm

# Установка системных зависимостей для Windows
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    postgresql-client \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath 

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создание рабочей директории
WORKDIR /var/www

# Копирование файлов проекта
COPY . .

# Установка прав 
RUN chmod -R 775 storage bootstrap/cache

# Кэширующий слой - копируем только composer файлы сначала
COPY composer.json composer.lock ./
# Установка зависимостей Composer
RUN composer install --no-dev --optimize-autoloader

# Создание .env файла если его нет
RUN if [ ! -f .env ]; then cp .env.example .env && php artisan key:generate; fi

EXPOSE 8000