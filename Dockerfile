FROM php:5.6-apache
MAINTAINER Stephan Zednik "zednis2@rpi.edu"
ENV REFRESHED_AT 2015-03-15

#RUN pear channel-discover pear.apache.org/log4php && \
#    pear install log4php/Apache_log4php && \
#    docker-php-ext-install log4php

ENV WWW_DATA /var/www/html
ENV SERVICE_HOME ${WWW_DATA}/search

RUN mkdir -p ${WWW_DATA}/search

#COPY config/php.ini /usr/local/etc/php

ADD rds.php ${SERVICE_HOME}/rds.php
ADD rds.ttl ${SERVICE_HOME}/rds.ttl
ADD opensearch.xml ${SERVICE_HOME}/opensearch.xml
ADD search.php ${SERVICE_HOME}/search.php
ADD index.html ${SERVICE_HOME}/index.html

COPY s2s/opensearch/ ${SERVICE_HOME}/
COPY s2s/client/ ${SERVICE_HOME}/s2s/client/
COPY js/ ${SERVICE_HOME}/js/
COPY css/ ${SERVICE_HOME}/css/

#TODO update RDF URIs with sed

VOLUME /var/www/html

#RUN mkdir -p /etc/rds
#ADD rds.ini /etc/rds/rds.ini
