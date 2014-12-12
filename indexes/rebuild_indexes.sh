#!/bin/bash

while read line
do
    echo "Updating: $line"
    php export_index.php $line
done < indexes.txt

echo "Updating: Case mappings"
php build_case_mappings.php
