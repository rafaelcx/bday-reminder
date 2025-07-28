FROM php:8.4-cli

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    cron \
    && rm -rf /var/lib/apt/lists/*

# Installs Composer globally and copy files
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock ./

# Installs PHP dependencies using Composer
RUN composer install --no-autoloader --no-scripts

COPY . .

RUN composer dump-autoload

# Copy the cron definition
COPY cron/crontab.txt /etc/cron.d/birthday-cron
RUN chmod 0644 /etc/cron.d/birthday-cron && crontab /etc/cron.d/birthday-cron

# Create log file
RUN touch /var/log/cron.log

# Ensure startup script is executable
RUN chmod +x bin/start.sh

# Expose HTTP port
EXPOSE 8000

# Start both PHP server and cron
CMD [ "sh", "bin/start.sh" ]
