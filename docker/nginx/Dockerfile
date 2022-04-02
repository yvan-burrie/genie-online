FROM nginx:stable-alpine

ADD ./nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html

RUN mkdir -p /etc/nginx/certs/mkcert

ADD ./nginx/certs /etc/nginx/certs/mkcert

ENV NGINX_GROUP=genie
ENV NGINX_USER=genie
RUN adduser -g ${NGINX_GROUP} -s /bin/sh -D ${NGINX_USER}
