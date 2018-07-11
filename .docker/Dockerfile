##
#   Peerocind and php software installed
#   following software:
#       - Ubuntu 18.04 LTS
#       - peercoind
#       - supervisor
#       - CLI apps: vim, curl, wget, buld-essensials
#
#   Disclamer: This Docker file is used as is, without any warranty!
##

FROM ubuntu:18.04
MAINTAINER Ante Braovic <abraovic@gmail.com>

ENV DEBIAN_FRONTEND noninteractive

RUN usermod -u 1000 www-data
RUN apt-get update && apt-get install -y \
    supervisor \
    vim \
    wget \
    unzip \
    php${PHP_VERSION} \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-dev \
    php${PHP_VERSION}-gmp \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-zip \
&& rm -rf /var/lib/apt/lists/*

# peercoind install
COPY ./.docker/config/install.sh /tmp/install.sh
RUN chmod +x /tmp/install.sh
RUN /tmp/install.sh
COPY ./.docker/config/peercoin.conf /root/.peercoin/peercoin.conf

## Configure
COPY ./.docker/config/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

CMD ["/usr/bin/supervisord"]