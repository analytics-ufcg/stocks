#!/bin/bash

### EXECUTAR PARA FAZER DEPLOYMENT DA APLICACAO

target_host=$1

rm stocks.zip

zip -r stocks.zip stocks

scp stocks.zip stocks@${target_host}:~/

ssh stocks@${target_host} "cd /var/www/; rm -r stocks/*; unzip ~/stocks.zip -d ."

rm -rf stocks.zip
