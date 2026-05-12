ARG PHP_VERSION=8.5

######################
# Test runner target #
######################
FROM milejko/php:${PHP_VERSION}

ENV XDEBUG_ENABLE=1 \
    XDEBUG_MODE=coverage

RUN set -eux; \
    echo "apc.enable_cli=1" >> /etc/php/${PHP_VERSION}/mods-available/apcu.ini
