#!/usr/bin/env bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
LDAP_DB=/tmp/ldap_db

echo "Creating database directory"

rm -rf ${LDAP_DB} && mkdir ${LDAP_DB} && cp  /usr/share/doc/slapd/examples/DB_CONFIG ${LDAP_DB}

cp ${DIR}/OpenLDAP/certs/IntegrationTestCA/root-ca.crt /tmp
cp ${DIR}/OpenLDAP/certs/*.crt /tmp
cp ${DIR}/OpenLDAP/certs/*.key /tmp

echo "Launching OpenLDAP ..."

# Start slapd with non root privileges
slapd -h "ldap://0.0.0.0:3890/ ldaps://0.0.0.0:6360" -f ${DIR}/OpenLDAP/slapd.conf

echo "Launching a PHP built-in webserver on port 3891..."
nohup php -S 0.0.0.0:3891 --docroot ${DIR}/php_scripts 2>&1 > /dev/null &

# Wait for LDAP to start
sleep 2
