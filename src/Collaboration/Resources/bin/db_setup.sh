#!/bin/bash
# -------------------------------------------
# IDDD collaboration views database setup
# -------------------------------------------

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo Creating IDDD Collaboration Event Store and Views database...
mysql -u root -p < "$DIR/../sql/collaboration.sql"

echo Completed