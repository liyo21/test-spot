# test-spot-backend

## Descripción
Este backend está construido utilizando Laravel 10, Octane y PHP 8.1. Su objetivo principal es generar nuevas url shortened, listarlas, consultar por una en especifico y eliminarlas.

## Requisitos previos
* **PHP:** Versión 8.1 o superior.
* **Composer:** Para la gestión de dependencias.

## Opciones de instalación
Para levantar este proyecto disponemos de dos formas, uno a traves de ambiente de docker, que para ello deben revisar el readme que se encuentra dentro de la carpeta dev la cual es un submodulo de Git y la otra de la forma tradicional

## Construcción

```bash
# Ingresar al directorio app
$ cd app

# instalar dependencias de composer
$ composer update

# generar el .env
$ cp .env.example .env

# generar la key para laravel
$ php artisan key:generate

# brindar los permisos para la carpeta storage
$ chown -R www-data:www-data storage/

# Correr laravel con Octane
$ php artisan octane:start --watch --host=0.0.0.0 --port=9000
```

## Unit Test

```bash
# Ingresar al directorio app
$ cd app

# instalar dependencias de composer
$ composer update

# generar el .env.testing
$ touch .env.testing

# setear las siguientes variables
$ php artisan key:generate
```

|Variable        |Valor                          |Descripción                  |
|----------------|-------------------------------|-----------------------------|
|APP_NAME        | "Test para Spot-2"            |                             |
|APP_ENV         | testing                       |                             |
|APP_KEY         | base64:2ODGYr3HnRsYvf5pCoZigTKjg5eV5qS07BleQEw01is= |       |
|APP_DEBUG       | true                          |                             |
|APP_TIMEZONE    | America/Santiago              |                             |
|APP_URL         | http://test-spot-two.loc:31080 |                            |
|DB_CONNECTION   | sqlite                        |                             |
|DB_DATABASE     | :memory:                      |                             |


## Unit Test

```bash
# Luego ejecutar las pruebas unitarias
$ php artisan test

```