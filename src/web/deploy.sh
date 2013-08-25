#!/bin/bash

### EXECUTAR PARA FAZER DEPLOYMENT DA APLICACAO

rm stocks.zip

zip -r stocks.zip stocks

scp stocks.zip stocks@150.165.15.171:~/

ssh stocks@150.165.15.171 "cd /var/www/; rm -r stocks/*; unzip ~/stocks.zip -d ."

rm -rf stocks.zip