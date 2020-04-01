FROM uogsoe/soe-php-apache:7.1

WORKDIR /var/www/html/
#- make paths that the laravel composer.json expects to exist
RUN mkdir -p database
#- copy the seeds and factories so that composer generates autoload entries for them
COPY database/seeds database/seeds
COPY database/factories database/factories
COPY composer* /var/www/html/
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist
COPY . /var/www/html/
RUN cp .env.gitlab .env
RUN php artisan key:generate
CMD ["./vendor/bin/phpunit", "--stop-on-defect"]
