# Birthday Reminder Tool

This is a simple tool designed to help you remember you close ones' birthdays by automatically adding them as recurring events, periodically forwarded to messaging apps.

---

## Provisioning

Ensure you have Docker and Docker Compose installed on your local machine. Then, run the following commands:

```
docker compose build --no-cache
docker compose up
```

This will build the container, install all necessary dependencies, and start a PHP built-in web server, which will be accessible at:

[http://localhost:8000/](http://localhost:8000/)

It will not start the cron service tough. To enable cron for testing purposes or your productive environment you can set the proper env var at the `.env` file.

## Storage

This project uses SQLite as its persistent storage layer. The database file lives at `db/database.sqlite` and is provisioned from the database migration and seed scripts.

To create the required tables and seed the initial records, run:

```
docker compose exec bday-reminder php bin/db-migrate.php
docker compose exec bday-reminder php bin/db-seed.php
```

The migration script is intended for local setup and production provisioning alike. The migration script creates all app tables, and the seed script (for local development) inserts the initial default data. Running them again is safe for schema creation and should not duplicate rows when the underlying database action is idempotent.

## Tests

To run the PHPUnit tests for the app, run the following command:

```
docker compose exec bday-reminder vendor/bin/phpunit
```
