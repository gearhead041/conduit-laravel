# RealworldApp Implementation in Laravel

## Description
This is a backend implementation of the [Realworld App](https://demo.realworld.io/) using [Laravel](https://laravel.com/).




## Installation
1. Clone the repository
2. Run `composer install`
3. Run `php artisan key:generate`
4. Run `php artisan migrate`
5. Run `php artisan serve`

## Testing with Newman
1. Install [Newman](https://www.npmjs.com/package/newman)
2. Run the following command
```bash
newman run Conduit.postman_collection.json --global-var "APIURL=http://127.0.0.1:8000/api" --global-var "USERNAME=John Doe" --global-var  "EMAIL=user@example.com" --global-var "PASSWORD=passworded"
```

## Things to still do
- [ ] Add test with laravel dusk
- [ ] Some tests not passing on newman collection, could be because it's old? Sometimes it's not seeing properties that are there. Should go through and rewrite the tests.