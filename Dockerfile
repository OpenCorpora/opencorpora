FROM debian:bullseye

RUN apt update
RUN apt install -y apache2 git subversion curl cron \
                   libconfig-ini-perl libdbd-mysql-perl libdbi-perl libxml-parser-perl \
                   php7.4 php7.4-mysql php7.4-curl php7.4-intl libapache2-mod-php7.4 php7.4-mbstring php7.4-xml

WORKDIR /var/www/html
RUN rm -rf /var/www/html/* && git clone https://github.com/OpenCorpora/opencorpora.git .
RUN curl -sS https://getcomposer.org/installer | php
RUN php composer.phar self-update --1
RUN php composer.phar install

WORKDIR /var/www
RUN mkdir -p smarty_dir/cache && mkdir -p smarty_dir/configs && mkdir -p smarty_dir/templates_c
RUN chown -R www-data:www-data smarty_dir

WORKDIR /var/www/html
RUN sed -E 's/^(host\s+=\s+)127\.0\.0\.1/\1opencorpora_db/' config.default.ini | \
    sed -E 's/^(template_dir\s+=\s+)\/var\/www\/templates\//\1\/var\/www\/html\/templates\//'  > config.ini
RUN sed -E 's/^(\s+\"host\"\s*:\s*\")127\.0\.0\.1/\1opencorpora_db/' config.default.json | \
    sed -E 's/^(\s+\"template_dir\"\s*:\s+\")\/var\/www\/templates\//\1\/var\/www\/html\/templates\//' > config.json
RUN mkdir logs && chown -R www-data:www-data logs

RUN ln -s /var/www/html /corpus

CMD ["apachectl", "-D", "FOREGROUND"]

