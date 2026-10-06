# Recruitment Task: Symfony API Products

[![CI](https://github.com/gander/Recruitment-Task__Symfony-API-Products/actions/workflows/ci.yml/badge.svg)](https://github.com/gander/Recruitment-Task__Symfony-API-Products/actions/workflows/ci.yml)

Zadanie: REST API w Symfony z jednym endpointem `POST /api/products`, który dodaje produkt do bazy SQLite. Żądanie JSON jest walidowane (puste lub niepoprawne body i błędne pola zwracają 400 z listą błędów), a poprawny produkt zapisuje się przez Doctrine i zwraca 201 z jego identyfikatorem.

## Requirements

- Docker Engine z Docker Compose v2 (jedyna zależność; PHP ani Composer na hoście nie są potrzebne).
- `curl` do przykładów użycia.

## Install

```bash
docker compose up --build -d --wait
```

Kontener przy starcie wykonuje migracje Doctrine.

## Usage

```bash
curl --retry 30 --retry-all-errors --retry-delay 2 \
  --request POST \
  --url http://localhost:8000/api/products \
  --header 'content-type: application/json' \
  --data '{"name": "Foo Bar","price": "123.45"}'
```

Pierwsza odpowiedź może potrwać kilka sekund. Przykład błędnego żądania (400):

```bash
curl --request POST \
  --url http://localhost:8000/api/products \
  --header 'content-type: application/json' \
  --data '{}'
```

## Test

Projekt nie zawiera testów; CI uruchamia `composer validate`, `composer audit` i `docker compose config`.

```bash
docker compose run --rm --no-deps app composer validate --no-check-publish
```

## Override

Lokalne zmiany (np. montowanie kodu do kontenera) trzymaj w `compose.override.yml`, ignorowanym przez git:

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
