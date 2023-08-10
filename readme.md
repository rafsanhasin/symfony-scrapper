# Instructions to run this project

#### 1. Clone this project from github:
`git@github.com:rafsanhasin/symfony-scrapper.git`

#### 2. Configure docker compose:
`docker-compose.yml` from `docker-compose-example.yml`

#### 3. Build and run docker:
`docker-compose build && docker-compose up -d`

#### 4. Configure ENV:
`.env` from `.env,example`

#### 5. Update dependencies:
`docker exec -it <php-container-name> composer update`

#### 6. To create DB: 
`docker exec -it <php-container-name> php bin/console doctrine:database:create`

#### 7. Make migration: 
* `docker exec -it <php-container-name> mkdir migrations`
* `docker exec -it <php-container-name> php bin/console make:migration`

#### 8. Apply migration changes to DB: 
`docker exec -it <php-container-name> php bin/console doctrine:migrations:migrate`

#### 9. Application Url (Default): 
`http://localhost`

#### 10. RabbitMQ Dashboard Url (Default): 
`http://localhost:15672`

## Important Note
* If there is Cloudflare Verification on visiting the original site then use VPN and set your
 location to USA(I have tested with this location).

