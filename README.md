# ShopDropDown

<!-- on local -->
docker build --platform linux/amd64 -t nfcdjobo/shopdropdown:latest .
docker push nfcdjobo/shopdropdown:latest

<!-- on vps -->
cd /var/www/shopdropdown
sudo docker compose pull web
sudo docker compose up -d

php artisan optimize:clear
php artisan queue:work     

