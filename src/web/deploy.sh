#!/bin/bash

### EXECUTAR PARA FAZER DEPLOYMENT DA APLICACAO

rm stocks.zip

zip -r stocks.zip stocks

scp stocks.zip stocks@150.165.15.6:~/

ssh stocks@150.165.15.6 "cd /var/www/; rm -r stocks/*; unzip ~/stocks.zip -d ."

rm -rf stocks.zip
