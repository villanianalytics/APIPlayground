# APIPlayground

## Docker
### Build
docker-compose build --force-rm

### Compose (start applications)
docker-compose up -d --force-recreate

### Docker status
docker ps -a

### Enter docker php container
docker exec -it dummy-php bash

## Update dependencies
composer update

## Database
php bin/console doctrine:database:create --if-not-exists 
php bin/console doctrine:migrations:migrate -n 

## Basic auth
Username: admin
Password: admin

## Token auth
Required header
Key: Authorization
Value: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c