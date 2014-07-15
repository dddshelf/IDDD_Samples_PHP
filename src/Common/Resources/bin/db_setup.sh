#!/bin/bash
# -------------------------------------------
# IDDD Common database setup
# -------------------------------------------

MYSQL_USER=root
MYSQL_PASSWORD=ones2502
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo Creating IDDD Common test database...
cat "$DIR/../sql/test_common.sql" > create_test_common.sql
cat "$DIR/../sql/common.sql" >> create_test_common.sql
mysql -u${MYSQL_USER} -p${MYSQL_PASSWORD} < create_test_common.sql
rm -f create_test_common.sql

echo Completed