#!/bin/sh
cp -Rf /usr/share/elasticsearch/config/certs /usr/share
chown -Rf www-data:www-data /usr/share/certs
exec "$@"
