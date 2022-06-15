# Symfony 6: The fast track

Project from the [Symfony book](https://symfony.com/doc/6.0/the-fast-track/en/index.html)

## Requirements

* PHP>=8.0
* Composer
* Docker
* Symfony CLI
* NodeJS (npm)

## Installation

Start docker

```bash
docker-compose up -d
```

Start dev server

```bash
symfony server:start -d
```

Start messenger service

```bash
symfony run -d symfony console messenger:consume async -vv
```

NPM

```bash
npm install
npm run dev-server
```