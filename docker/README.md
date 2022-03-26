# Docker Containerization

## Building Services

Build all necessary services:

```shell
docker-compose build nginx apache phpunit dev mysql node npm yarn lua
```

## Running Essential services

Run the Nginx service:

```shell
docker-compose up -d nginx
```

Or, run the Apache service:

```shell
docker-compose up -d apache
```

Running the `nginx` or `apache` services will also start the `php` and `mysql` services.

Also, run other services:
```shell
docker-compose up -d redis pma
```

## Running Command Services

Run command services with entry-point:

```shell
docker-compose run --rm git
docker-compose run --rm phpunit
docker-compose run --rm node
docker-compose run --rm npm
docker-compose run --rm yarn
docker-compose run --rm lua
```
