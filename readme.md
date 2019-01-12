# Desafio

### Installation

- Make sure you have php and composer installed. https://lumen.laravel.com/docs/5.7
- Configure your .env file with database information.

```sh
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dienekes
DB_USERNAME=username    
DB_PASSWORD=password
```

Run this commands to setup the database.

```sh
php artisan migrate
php artisan extract
```

When finished, you can see all the sorted numbers running the built-in server.

```sh
php -S localhost:8000 -t public
```

Go to the endpoint.

```sh
http://localhost:8000/api/numbers
```

If possible, dont visit this endpoint at your browser or some chrome basead version of Postman (check the new native app at https://www.getpostman.com/). Too many nodes in DOM would probably make your browser crash.