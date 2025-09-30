# Data Mapper API

A simple API that can be installed in other projects that exposes the [Data Mapper library](https://github.com/dealnews/data-mapper) in said project to other services.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Contributing](#contributing)

## Requirements

- [Data Mapper](https://github.com/dealnews/data-mapper)
- [DB](https://github.com/dealnews/db)

## Installation

```sh
composer require php-libraries/data-mapper-api
```

## Usage

Before using this library, you will need a `\DealNews\DataMapper\Repository` object that maps your data mappers to object names if you
have not already created one.

```php
$repo = new \DealNews\DataMapper\Repository();
$repo->addMapper("Example", new \DealNews\Example\Mapper());
```

You will also need a router of some sort to route API requests to the appropriate code/action. For most cases, you can
use the [PageMill Router](https://github.com/dealnews/pagemill-router). To help with building routes for the PageMill
Router, this library comes with some pre-defined routes and a helper method to make sure input data is properly formatted
for the API endpoint.

Routing for just one specific endpoint (if you're only interested in using a portion of the provided endpoints/actions from this library):
```php
$api = new \DealNews\DataMapperAPI\API();

$routes = [
    $api->getRoute('get_object_route')
];

$router = new \PageMill\Router\Router($routes);

$route = $router->match();

if (!empty($route)) {
    $api->executeAction($route['action'], $route['tokens'], 'https://example.com', $repo);
}
```

Routing for all provided endpoints:
```php
$api = new \DealNews\DataMapperAPI\API();

$router = new \PageMill\Router\Router($api->getAllRoutes());

$route = $router->match();

if (!empty($route)) {
    $api->executeAction($route['action'], $route['tokens'], 'https://example.com', $repo);
}
```

By default, all endpoint paths have a prefix of `/api`. However, you can change this by passing a different prefix to
either `getRoute()` or `getAllRoutes()` methods. Example:
```php
$api = new \DealNews\DataMapperAPI\API();

$router = new \PageMill\Router\Router($api->getAllRoutes('/different-api-path-prefix'));

$route = $router->match();

if (!empty($route)) {
    $api->executeAction($route['action'], $route['tokens'], 'https://example.com', $repo);
}
```

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of
conduct, and the process for submitting merge requests for this project.
