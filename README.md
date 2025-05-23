# Symfony Docker

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Features

This stack includes the following features:
- **User Management**: Built-in functionality for user creation, login, and registration.
- **Secure Authentication**: Pre-configured authentication system using Symfony's security component.
- **Dockerized Environment**: Fully containerized setup for easy development and deployment.

## Tools

1. Add services to Symfony, run `./bin/service_add.sh` and select the service you want to add.

## Docs

1. [Options available](docs/options.md)
2. [Using Symfony Docker with an existing project](docs/existing-project.md)
3. [Support for extra services](docs/extra-services.md)
4. [Deploying in production](docs/production.md)
5. [Debugging with Xdebug](docs/xdebug.md)
6. [TLS Certificates](docs/tls.md)
7. [Using MySQL instead of PostgreSQL](docs/mysql.md)
8. [Using Alpine Linux instead of Debian](docs/alpine.md)
9. [Using a Makefile](docs/makefile.md)
10. [Updating the template](docs/updating.md)
11. [Troubleshooting](docs/troubleshooting.md)

## Authentication

This stack comes pre-configured with user authentication features:
- **Registration**: Users can register via a `/register` endpoint.
- **Login**: Users can log in via a `/login` endpoint.
- **User Management**: Includes basic user creation and management functionality.

For more details on how to customize or extend the authentication system, refer to the Symfony [Security documentation](https://symfony.com/doc/current/security.html).