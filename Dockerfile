FROM php:8.3-cli

RUN apt-get update && apt-get install -y git unzip && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
