#!/bin/bash -e

function getPodName() {
    kubectl -n $NAMESPACE get pods -lapp=$1 -o json | jq '.items[0].metadata.name' --raw-output
}

function transferFilesToPod() {
    SRC=$1
    POD=$2
    POD_PATH=$3

    echo "Copying $PUBLIC_FILES to $POD:$POD_PATH"
    tar -zcC $SRC . \
        | kubectl -n $NAMESPACE exec $POD -i tar zxC $POD_PATH
}

function importSqlToPod() {
    DUMP=$1
    POD=$2
    DB=$3

    echo "Importing $1 to $POD:$DB"

    function mysql() {
        DATABASE=$1
        kubectl -n $NAMESPACE exec -i $POD -- bash -c "mysql -u root -p\$MYSQL_ROOT_PASSWORD $DATABASE"
    }

    echo "CREATE DATABASE $DB;" | mysql
    echo "SHOW DATABASES;" | mysql
    cat $DUMP | mysql $DB
    echo "SHOW TABLES;" | mysql $DB
}

NAMESPACE=${NAMESPACE:-default}
PUBLIC_FILES=${PUBLIC_FILES:-./public}
PUBLIC_FILES_POD_PATH=${PUBLIC_FILES_PATH:-/data/public/}
PRIVATE_FILES=${PRIVATE_FILES:-./private}
PRIVATE_FILES_POD_PATH=${PRIVATE_FILES_PATH:-/data/public/}
MYSQL_DUMP=${MYSQL_DUMP:-database.sql}

DRUPAL_POD=$(getPodName drupal)
echo "Drupal pod is $DRUPAL_POD"

MYSQL_POD=$(getPodName mysql)
echo "Mysql pod is $MYSQL_POD"

[ -d "$PUBLIC_FILES" ] && transferFilesToPod $PUBLIC_FILES $DRUPAL_POD $PUBLIC_FILES_POD_PATH
[ -d "$PRIVATE_FILES" ] && transferFilesToPod $PRIVATE_FILES $DRUPAL_POD $PRIVATE_FILES_POD_PATH
[ -f "$MYSQL_DUMP" ] && importSqlToPod $MYSQL_DUMP $MYSQL_POD drupal
