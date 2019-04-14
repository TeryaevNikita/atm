docker-compose up

docker-compose exec php-fpm php yii migrate

5 routes

GET localhost/atm/1/balance - get balance
GET localhost/atm/1/notes - get notes
GET localhost/atm/1/statistics - get statistics
POST localhost/atm/1/fill - add notes
{
    "100": 110,
    "200": 40,
    "500": 30,
    "1000": 12,
    "5000": 6
}

POST localhost/atm/1/withdrawal - withdrawal money
{
    "amount": "26000",
    "currency": "RUB"
}