#!/bin/bash
/etc/init.d/php8.3-fpm start
nohup nginx -g "daemon off;" > /var/log/nginx.log 2>&1 &
tail -f /dev/null