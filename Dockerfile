FROM phusion/baseimage:0.9.15
MAINTAINER Stephan Zednik <zednis2@rpi.edu>

ENV HOME /root

RUN apt-get -qq update

RUN apt-get install -y php5 php-apc php5-fpm git

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN git clone https://github.com/tetherless-world/rds-s2s.git

WORKDIR rds-s2s
RUN git submodule init && git submodule update

ENV WWW_DATA /var/www/html
RUN mkdir -p ${WWW_DATA}/rds

ADD s2s/opensearch/config.php ${WWW_DATA}/rds/config.php
ADD s2s/opensearch/utils.php ${WWW_DATA}/rds/utils.php

RUN mkdir -p /etc/rds
ADD rds.ini /etc/rds/rds.ini

ADD opensearch.xml ${WWW_DATA}/rds/opensearch.xml
ADD rds.php ${WWW_DATA}/rds/rds.php
ADD rds.ttl ${WWW_DATA}/rds/rds.ttl

# VIVO_ENDPOINT?
# VIVO_URL_PREFIX

RUN sed -e 's/^listen.*/listen = 9000/' \
        -e '/allowed_clients/d' \
        -e '/catch_workers_output/s/^;//' \
        -e '/error_log/d' \
        -i /etc/php5/fpm/pool.d/www.conf

EXPOSE 9000

ENTRYPOINT /usr/sbin/php5-fpm --nodaemonize
