FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive 

RUN \
    apt-get update && \
    apt install software-properties-common -y && \
    apt-get --no-install-recommends install -y nginx curl cron &&\
    add-apt-repository ppa:ondrej/php &&\
    apt install -y php8.3-dev && \
    apt install -y php8.3 libapache2-mod-php8.3 php8.3-mysql \
    php8.3-cli php8.3-common php8.3-fpm php8.3-soap php8.3-gd \
    php8.3-opcache  php8.3-mbstring php8.3-zip \
    php8.3-bcmath php8.3-intl php8.3-xml php8.3-curl  \
    php8.3-imap php8.3-ldap php8.3-gmp php8.3-redis \
    php8.3-memcached sudo php8.3-sqlite3 php-sqlite3

COPY /nginx /etc/nginx
COPY scripts/start.sh /start.sh

RUN chmod +x /start.sh && rm /var/www/html/index.nginx-debian.html

CMD ["/start.sh"]