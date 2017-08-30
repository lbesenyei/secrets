# Secret Server

Secret server laravel package.
This application can store and serve secret messages based on a unique hash for a limited time or number of times

### Dependencies

- laravel `5.4`: [https://laravel.com/](https://laravel.com/)

### Installation

1. Install laravel 5.4

 [https://laravel.com/docs/5.4#installation](https://laravel.com/docs/5.4#installation)

2. Clone application. Run from laravel root directory

 `git clone https://github.com/lbesenyei/secrets.git packages/lbesenyei/secrets`

3. Update laravel main `composer.json` file to autoload the package

 ```
...
"autoload": {
  "classmap": [
    "database"
  ],
  "psr-4": {
    "App\\": "app/",
    "Lbesenyei\\Secrets\\": "packages/lbesenyei/secrets/src"
  }
},
...
```

4. Dump composer autoload

 `composer dump autoload`

5. Add `Lbesenyei\Secrets\SecretsServiceProvider::class` to the `config/app.php` file

 ```
...
'providers' => [
  ...
  Lbesenyei\Secrets\SecretsServiceProvider::class,
  ...
],
...
```

6. Create table by running `php artisan migrate`.

 `php artisan migrate --path=packages/lbesenyei/secrets/src/migrations/`


### Usage

- ##### Creating a secret:

 Post an json data to `http://yourdomain.com/secret` path with the following data structure:

 ```
 {
  "secret":"Secret Message",
  "expireAfterViews":10,
  "expireAfter":10
}
 ```

 ##### Response:

 ```
 {
   "hash": "3a323c7ebeed939a2c74ec4f383f4c1c",
   "secretText": "Secret Message",
   "createdAt": 1504089035,
   "expiresAt": 0,
   "remainingViews": 10
 }
 ```

- ##### View a secret:

 Access the following url: `http://yourdomain.com/secret/3a323c7ebeed939a2c74ec4f383f4c1c`

 ##### Response

  ```
 {
   "hash": "3a323c7ebeed939a2c74ec4f383f4c1c",
   "secretText": "Secret Message",
   "createdAt": 1504089035,
   "expiresAt": 0,
   "remainingViews": 9
 }
 ```
