# Recruitment Task: Symfony API Products

[![CI](https://github.com/gander/Recruitment-Task__Products/actions/workflows/ci.yml/badge.svg)](https://github.com/gander/Recruitment-Task__Products/actions/workflows/ci.yml)

Task: a Symfony REST API with a single `POST /api/products` endpoint that adds a product to a SQLite database. The JSON request is validated (an empty or invalid body and invalid fields return 400 with a list of errors); a valid product is saved through Doctrine and returned with 201 and its identifier.

## Requirements

- Docker Engine with Docker Compose v2 (the only dependency; neither PHP nor Composer is needed on the host).
- `curl` for the usage examples.

## Install

```bash
docker compose up --build -d --wait
```

On start the container runs the Doctrine migrations.

## Usage

```bash
curl --retry 30 --retry-all-errors --retry-delay 2 \
  --request POST \
  --url http://localhost:8000/api/products \
  --header 'content-type: application/json' \
  --data '{"name": "Foo Bar","price": "123.45"}'
```

The first response may take a few seconds. Example of an invalid request (400):

```bash
curl --request POST \
  --url http://localhost:8000/api/products \
  --header 'content-type: application/json' \
  --data '{}'
```

## Test

Unit tests (PHPUnit) cover the controller, the entity and the violations mapper:

```bash
docker compose run --rm --no-deps app vendor/bin/phpunit
```

CI additionally runs `composer validate`, `composer audit`, `docker compose config`, Rector and ECS.

## Override

Keep local changes (e.g. mounting the code into the container) in `compose.override.yml`, which is ignored by git:

```bash
cat > compose.override.yml <<'OVERRIDE'
services:
  app:
    volumes:
      - .:/app
OVERRIDE
docker compose up --build -d --wait
```

## Cleanup

```bash
docker compose down -v --rmi local --remove-orphans
rm -f compose.override.yml
```

## License

This project is licensed under the [PolyForm Noncommercial License 1.0.0](https://polyformproject.org/licenses/noncommercial/1.0.0)
with additional terms (see [LICENSE](LICENSE)). In short: you may read the code and run it to evaluate the
author's job application, but you may not use it commercially, in your company's operations, or as assessment
material in any other hiring process.
