#!/bin/bash
# -------------------------------------------
# IDDD Common database setup
# -------------------------------------------

echo Creating IDDD IdentityAccess database...
cat ../sql/iam.sql > create_iam.sql
cat ../../../Common/Resources/sql/common.sql >> create_iam.sql
mysql -u root -p < create_iam.sql
rm -f create_iam.sql

echo Completed