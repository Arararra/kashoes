# Langkah setup awal
### Pastikan `.env` dan database sudah disetup
```shell
$ composer install

$ php artisan key:generate

$ php artisan migrate:fresh --seed
```

# Langkah pull update
```shell
$ composer install

$ php artisan migrate
```

# Langkah menjalankan project
```shell
$ php artisan serve
```
