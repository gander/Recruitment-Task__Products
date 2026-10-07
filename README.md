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

Create a product (the first response may take a few seconds):

```bash
curl --retry 30 --retry-all-errors --retry-delay 2 \
  --request POST \
  --url http://localhost:8000/api/products \
  --header 'content-type: application/json' \
  --data '{"name": "Foo Bar","price": "123.45"}'
```

Expected response, status 201:

```json
{"status":true,"product":1}
```

An invalid request:

```bash
curl --request POST \
  --url http://localhost:8000/api/products \
  --header 'content-type: application/json' \
  --data '{}'
```

Expected response, status 400:

```json
{"status":false,"errors":[{"property":"name","message":"This value should not be blank."},{"property":"price","message":"This value should not be blank."}]}
```

## Test

Run the whole test suite (PHPUnit) in Docker:

```bash
docker compose run --rm --build --no-deps app composer test
```

Run the quality checks (Rector dry run and ECS), which CI runs too:

```bash
docker compose run --rm --build --no-deps app composer check
```

CI (`.github/workflows/ci.yml`) runs the jobs `checks` (`composer validate`, `composer audit`, `docker compose config`), `quality`, `tests` (PHP 8.4, plus a non-blocking PHP 8.5 run), `outdated` and `smoke` (builds the image and replays the requests from Usage). Optional pre-commit hooks that run Rector, ECS and `swiss-knife breakpoint` in Docker: `lefthook install`.

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
