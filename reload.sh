#!/bin/sh

cd /home/hyperf-v3 && git checkout . && git pull && echo -e "\n" | composer update && supervisorctl restart hyperf