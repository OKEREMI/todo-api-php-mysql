# Todo API (PHP + MySQL)

A simple Todo List REST API built with PHP and MySQL, running in Docker Compose. Includes a small HTML/JavaScript frontend for testing in the browser.

## Stack

- PHP 8.2 with Apache (php:8.2-apache)
- MySQL 8.0
- PDO with prepared statements for all database queries
- Docker Compose for orchestration

## Project structure

todo-api-php-mysql/
├── app/
│ ├── index.php # API routes (GET, POST, PUT, DELETE)
│ ├── db.php # Database connection
│ ├── init.sql # Table schema, runs automatically on first start
│ └── .htaccess # Routes all requests through index.php
├── frontend/
│ └── index.html # Simple browser frontend for testing
├── Dockerfile
├── docker-compose.yml
└── README.md


## Running the project

Make sure Docker and Docker Compose are installed, then from the project root run:

docker compose up --build -d


The API will be available at `http://localhost:8080`.

## Environment variables

These are already set in `docker-compose.yml`, no extra setup needed to run locally. In a real deployment, you'd override these instead of using the defaults:

| Variable | Description |
|---|---|
| DB_HOST | Hostname of the MySQL service (set to `db`, the Compose service name) |
| DB_NAME | Database name |
| DB_USER | Database user |
| DB_PASSWORD | Database user's password |

## API endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | /todos | List all todos |
| POST | /todos | Create a new todo (`{"title": "..."}`) |
| GET | /todos/{id} | Get a single todo |
| PUT | /todos/{id} | Update a todo (`title` and/or `completed`) |
| DELETE | /todos/{id} | Delete a todo |

Invalid input returns a proper 4xx status with a JSON error message, not a raw PHP error.

## Data persistence

MySQL data is stored in a named Docker volume, so it survives `docker compose down` and `docker compose up`. To wipe the data completely, run `docker compose down -v`.

## Frontend

A basic HTML page for testing the API in a browser lives in the `frontend/` folder. It can be served separately, for example with nginx or any static file server.

