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

This project uses json files as persistent storage. Files are stored at `storage/Files`. For local testing you will need to set then up. 

There are versioned sample files under the `/storage/Files/Templates` namespace, to copy then to
a working directory and seed them with arbitrary values you can run:

```
docker exec -it bday-reminder-bday-reminder-1 php /app/bin/storage-seed.php
```

## Tests

To run the PHPUnit tests for the app, run the following command:

```
docker compose exec bday-reminder ./vendor/bin/phpunit
```
