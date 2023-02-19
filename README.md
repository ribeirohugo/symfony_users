# symfony_users
Symfony Rest API to manage users.

## Summary

`Symfony Users` is a Web Application and a REST API service that allows to manage users by allowing to execute CRUD
operations through its built-in endpoints.
It is developed using an Onion Architecture, which allowed to turn all different layers decoupled and testable independently.

## Requirements

This project required Symfony `6.2.X` and PHP `8.2.X`.

## Commands

Run the following command to start the application.

``symfony server:start``

You can also set a port for this service by setting a `<port number>` value.

``symfony server:start --port=<port number>``

Run the following command to run tests.

``./vendor/bin/phpunit``
