FROM phusion/baseimage:0.9.15
MAINTAINER Stephan Zednik <zednis2@rpi.edu>

ENV HOME /root

RUN apt-get -qq update

RUN apt-get install -y php5 php-apc php5-fpm

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV WWW_DATA /var/www/html
RUN mkdir -p ${WWW_DATA}

ADD s2s/opensearch/config.php ${WWW_DATA}/rds/config.php
ADD s2s/opensearch/utils.php ${WWW_DATA}/rds/utils.php
ADD opensearch.xml ${WWW_DATA}/rds/opensearch.xml

RUN mkdir -p /etc/rds
ADD opensearch.ini /etc/rds/opensearch.ini

ADD rds.php ${WWW_DATA}/rds/rds.php
ADD rds.ttl ${WWW_DATA}/rds/rds.ttl

RUN sed -e 's/127.0.0.1:9000/9000/' \
        -e '/allowed_clients/d' \
        -e '/catch_workers_output/s/^;//' \
        -e '/error_log/d' \
        -i /etc/php-fpm.d/www.conf

# VIVO_ENDPOINT?
# VIVO_URL_PREFIX

RUN sed -e 's//' \
        -e '' \
        -i /etc/rds/opensearch.ini

EXPOSE 9000

ENTRYPOINT /usr/sbin/php-fpm --nodaemonize