#!/bin/bash
set -e
cd /tmp/deploy-shopdropdown
sed -i 's/\r$//' deploy-shopdropdown.sh docker-entrypoint.sh nginx-shopdropdown.conf .env
mkdir -p /var/www/shopdropdown
if [ -d "/var/www/shopdropdown/.git" ]; then
    git -C /var/www/shopdropdown pull
else
    git clone git@github.com:nfcdjobo/ShopDropDown.git /var/www/shopdropdown
fi
bash deploy-shopdropdown.sh
