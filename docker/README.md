# Docker Containerization

## Building Services

Build all services:

```shell
docker-compose build
```

Or, specifically build the necessary services:

```shell
docker-compose build nginx apache phpunit dev pma node npm yarn lua
```

## Running services

Run the Nginx service:

```shell
docker-compose up -d nginx
```

Or, run the Apache service instead:

```shell
docker-compose up -d apache
```

Note that running the `nginx` or `apache` services will also run the `php`, `mysql`, and `redis` services.

Optionally, run the PhpMyAdmin service:
```shell
docker-compose up -d pma
```

Then, open [PhpMyAdmin](http://localhost:8081/) in a browser.

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

## Service Ports

- `nginx` or `apache`: `80`
- `pma`: `81`
- `mysql`: `3306`
- `php`: `9000`
- `phpunit`: `9001`
- `dev`: `9002`
- `redis`: `6379`
