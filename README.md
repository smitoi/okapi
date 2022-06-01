# okAPI 🦓

```
Okapi is a large browsing mammal of the giraffe family that lives in the rainforests of northern Democratic Republic of Congo 
It has a dark chestnut coat with stripes on the hindquarters and upper legs.
```

## What is okAPI?

okAPI is a simple, work in progress headless content management system with API capabilities, built as a solution for
small to medium scale organisations.

Its main goal is to provide a way for data to be available outside the CMS through the API, with the platform either
being a monolith application, or being incorporated as a micro-service.

### Features

* clean and modern interface built with Vue on top of Laravel Breeze
* API that is automatically documented using OpenAPI specification
* vast array of fields - store your data any way you want
* relationships between different types of entities
* simple validation rules for the data
* users management with roles and permissions
* API keys for inter-service communication

### Possible future developments

* [ ] Tweaks to allow a more permissive fields / relationships system
* [ ] Advanced fields (TBD)
* [ ] Advanced validation (TBD)
* [ ] Integration for existing models / tables
* [ ] Functionality modularisation (plugin system)
* [ ] TypeScript support for Vue
* [ ] Event hooks - maybe with WebSockets (TBD)

## Local Development

This project uses [Laravel Sail](https://laravel.com/docs/sail) to manage its local development stack. For more detailed
usage instructions take a look at the [official documentation](https://laravel.com/docs/sail). You can find your
application at http://localhost. It also includes Mailhog on port 8025, MeiliSearch on port 7700, and Laravel Telescope
at http://localhost/telescope.

To start development you can use the following commands:

```shell
./vendor/bin/sail up -d
./vendor/bin/sail npm watch
```
