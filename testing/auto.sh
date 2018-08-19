#!/usr/bin/env bash

mkdir -p results

echo "warmup db"
# db warm up
wget --quiet \
      --method POST \
      --header 'Content-Type: application/json' \
      --header 'Cache-Control: no-cache' \
      --body-data "{\"clients\": 1000, \"intendedDuplicates\": 100}" \
      --output-document "./results/generate-warm-up.json" \
      - http://localhost:81/generateDb

for (( c = 100000; c <= 1000000; c*= 10 ))
do
    d=1000

    if ((c <= 10000))
    then
        d=100
    fi

    echo "generate db with $c clients of which $d are duplicates"

    wget --quiet \
          --method POST \
          --header 'Content-Type: application/json' \
          --header 'Cache-Control: no-cache' \
          --body-data "{\"clients\": $c, \"intendedDuplicates\": $d}" \
          --output-document "./results/generate-c$c-d$d.json" \
          - http://localhost:81/generateDb

    for m in 25 50 75 90
    do
        if (( c <= 10000))
        then
            echo "$c clients ($d duplicates), test SQL with threshold $m"
            # sql
            wget --quiet \
                  --method POST \
                  --header 'Content-Type: application/json' \
                  --header 'Cache-Control: no-cache' \
                  --body-data "{\"matchThreshold\": $m}" \
                  --output-document "./results/test-sql-c$c-d$d-m$m.json"\
                  - http://localhost:81/fetchDuplicatesSql
        fi

        echo "$c clients ($d duplicates), test PHP with threshold $m"

        # php
        wget --quiet \
              --method POST \
              --header 'Content-Type: application/json' \
              --header 'Cache-Control: no-cache' \
              --body-data "{\"matchThreshold\": $m}" \
              --output-document "./results/test-php-c$c-d$d-m$m.json"\
              - http://localhost:81/fetchDuplicatesPhp
    done
done