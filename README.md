# Genie Online

## Development Service

Copy the environment file:

```shell
sudo cp .env.dev.env .env
```

Edit the environment file:

```shell
sudo nano .env
```

Build and run the `dev` service:

```shell
sudo docker-compose build dev
sudo docker-compose up -d dev
```

Open the `dev` service in a new terminal window:

```shell
sudo docker-compose exec dev /bin/bash
```

## VS Code Local Development

Open project in VS Code and then reopen in remote container when the `dev` service is running.

## PhpStorm Local Development

Open project in PhpStorm.
