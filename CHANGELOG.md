# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][keepachangelog] and this project adheres to [Semantic Versioning][semver].

## v2.3.0

### Changed

- Laravel 8 is supported
- Minimal Laravel version now is `6.0` (Laravel `5.5` LTS got last security update August 30th, 2020)

## v2.2.0

### Changed

- Maximal `illuminate/*` packages version now is `7.*`
- Minimal `illuminate/*` packages version now is `5.6.*`
- CI completely moved from "Travis CI" to "Github Actions" _(travis builds disabled)_
- Minimal required PHP version now is `7.2`
- `config` directory was moved from `./src` to the root directory
- `DataMigrationsServiceProvider` renamed to `ServiceProvider`
- Minimal `symfony/console` version now is `^4.4` _(reason: <https://github.com/symfony/symfony/issues/32750>)_

### Added

- PHP 7.4 is supported now

## v2.1.0

### Changed

- Maximal `illuminate/*` packages version now is `6.*`

### Added

- GitHub actions for a tests running

## v2.0.0

### Added

- Docker-based environment for development
- Project `Makefile`

### Changed

- Minimal `PHP` version now is `^7.1.3`
- Maximal `Laravel` version now is `5.8.x`
- Dependency `laravel/framework` changed to `illuminate/*`
- Composer scripts
- Package contacts signatures _(method parameters types, method return types)_
- Service provider dependency `\Illuminate\Contracts\Foundation\Application` changed to `\Illuminate\Contracts\Container\Container`

### Removed

- Dev-dependency `avto-dev/dev-tools`

## v1.2.0

### Changed

- Maximal PHP version now is undefined
- Maximal Laravel version now is `5.7.*`
- Source code a little bit refactored
- CI changed to [Travis CI][travis]
- [CodeCov][codecov] integrated
- Issue templates updated

[travis]:https://travis-ci.org/
[codecov]:https://codecov.io/

## v1.1.0

### Changed

- CI config updated
- Minimal Laravel version up to `5.5`
- Minimal PHPUnit version up to `6.0`
- Integrated package `avto-dev/dev-tools` (dev)
- Integrated package `phpstan/phpstan` (dev)
- Unimportant PHPDoc blocks removed
- Code a little bit refactored

## v1.0.0

### Changed

- First release

[keepachangelog]:https://keepachangelog.com/en/1.0.0/
[semver]:https://semver.org/spec/v2.0.0.html
