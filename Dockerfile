FROM php:5.6-apache
MAINTAINER Stephan Zednik "zednis2@rpi.edu"
ENV REFRESHED_AT 2015-03-15

ENV WWW_DATA /var/www/html

WORKDIR ${WWW_DATA}

RUN mkdir -p ${WORKDIR}/search

#COPY config/php.ini /usr/local/etc/php

ADD rds.php ${WWW_DATA}/search/rds.php
ADD rds.ttl ${WWW_DATA}/search/rds.ttl
ADD opensearch.xml ${WWW_DATA}/search/opensearch.xml
ADD search.php ${WWW_DATA}/search/search.php
COPY s2s/opensearch/ ${WWW_DATA}/search/

RUN mkdir -p /etc/rds
ADD rds.ini /etc/rds/rds.ini

#RUN sed -e 's/^listen.*/listen = 9000/' \
#        -e '/allowed_clients/d' \
#        -e '/catch_workers_output/s/^;//' \
#        -e '/error_log/d' \
#        -i /etc/php5/fpm/pool.d/www.conf

#EXPOSE 9000
#ENTRYPOINT [ "/usr/sbin/php5-fpm", "--nodaemonize" ]
