<p align="center">
  <img src="https://laravel.com/assets/img/components/logo-laravel.svg" alt="Laravel" width="240" />
</p>

# Миграция данных БД для Laravel

[![Version][badge_packagist_version]][link_packagist]
[![PHP Version][badge_php_version]][link_packagist]
[![Build Status][badge_build_status]][link_build_status]
[![Coverage][badge_coverage]][link_coverage]
[![Downloads count][badge_downloads_count]][link_packagist]
[![License][badge_license]][link_license]

Данный пакет добавляет в ваше Laravel-приложение функционал мигрирования данных БД.

## Install

Require this package with composer using the following command:

```shell
$ composer require avto-dev/data-migrations-laravel "^2.0"
```

> Installed `composer` is required ([how to install composer][getcomposer]).

> You need to fix the major version of package.

Опубликуйте конфигурационный файл, при помощи которого вы можете переопределить имя таблицы в БД для хранения данных о миграциях, имя соединения и прочие настройки:

```bash
$ php ./artisan vendor:publish --provider="AvtoDev\\DataMigrationsLaravel\\ServiceProvider"
```

После чего отредактируйте файл `./config/data-migrations.php` на своё усмотрение и завершите установку, выполнив команду:

```bash
$ php ./artisan data-migrate:install
```

## Использование

Проблема, которую решает данный пакет - это отсутствие встроенного в Laravel механизма мигрирования "боевых" данных в ваше приложение (`seeds` это механизм заполнения фейковыми данными изначально, а миграции БД несут ответственность за схему и т.д., но _не_ данные).

Для того, что бы лучше ознакомиться с "механикой" работы данного пакета рассмотрим следующую ситуацию - ваше приложение использует 2 подключения к различным БД (`default` и `second_db`), и вам необходимо в каждом из них в уже существующие таблицы добавить какие-либо данные.

Для этого вам необходимо выполнить 2 команды:

```bash
$ ./artisan make:data-migration "Add some data into first table"
$ ./artisan make:data-migration --connection="second_db" "Add some data into second table"
```

После выполнения которых создадутся 2 файла:

- `./storage/data_migrations/2018_01_01_022000_add_some_data_into_first_table.sql`
- `./storage/data_migrations/second_db/2018_01_01_022001_add_some_data_into_second_table.sql`

Которые вы можете наполнить SQL-командами, производящими необходимые insert-ы и так далее (помните, что идеологически верно использовать их **только** для манипуляций с данными).

После этого вам достаточно выполнить:

```bash
$ ./artisan data-migrate
```

И данная команда произведёт поиск всех файлов (за исключением тех, чьи имена начинаются с точки) в директории `./storage/data_migrations` (путь может быть переопределен в конфигурационном файле) и попытается их выполнить, если запись об их выполнении не будет обнаружена в таблице `migrations_data` (которая была создана командой `data-migrate:install`).

## Особенности

При использовании данного пакета следует знать о следующих особенностях:

- Если в директории с файлами-миграциями данных `./storage/data_migrations` создать новую директорию, и уже в ней разместить файл-миграцию - то имя этой директории будет использовано как имя подключения к БД (описанное в файле `./config/database.php`), которое надо использовать для применения миграций, что в ней размещены;
- Для применения миграций используются специальные классы, реализующие интерфейс `ExecutorContract`. Вы можете создать свой, указав его полное имя в файле-конфигурации;
- Миграции могут быть упакованы с помощью `gzip` (`gzip file.sql`). При наличии установленного php-расширения `zlib` они распаковываются "на лету", главное чтоб имя файла миграции заканчивалось на `.gz`;
- Миграции не имеют механизма "отката" (rollback-ов).

### Artisan-команды

После установки данного пакета вам станут доступны следующие команды:

Сигнатура команды | Описание
----------------- | --------
`data-migrate:install` | Производит создание таблицы в БД для хранения данных о миграциях данных
`make:data-migration` | Создаёт файл-миграции (пустой) в соответствии с необходимыми правилами именования и расположения
`data-migrate` | Запускает механизм мигрирования данных
`data-migrate:status` | Выводит данные о примененных и не примененных миграциях
`data-migrate:uninstall` | Удаляет таблицу с данными о миграциях данных из БД

### Testing

For package testing we use `phpunit` framework and `docker-ce` + `docker-compose` as develop environment. So, just write into your terminal after repository cloning:

```bash
$ make build
$ make latest # or 'make lowest'
$ make test
```

## Changes log

[![Release date][badge_release_date]][link_releases]
[![Commits since latest release][badge_commits_since_release]][link_commits]

Changes log can be [found here][link_changes_log].

## Support

[![Issues][badge_issues]][link_issues]
[![Issues][badge_pulls]][link_pulls]

If you will find any package errors, please, [make an issue][link_create_issue] in current repository.

## License

This is open-sourced software licensed under the [MIT License][link_license].

[badge_packagist_version]:https://img.shields.io/packagist/v/avto-dev/data-migrations-laravel.svg?maxAge=180
[badge_php_version]:https://img.shields.io/packagist/php-v/avto-dev/data-migrations-laravel.svg?longCache=true
[badge_build_status]:https://img.shields.io/github/workflow/status/avto-dev/data-migrations-laravel/tests/master
[badge_coverage]:https://img.shields.io/codecov/c/github/avto-dev/data-migrations-laravel/master.svg?maxAge=60
[badge_downloads_count]:https://img.shields.io/packagist/dt/avto-dev/data-migrations-laravel.svg?maxAge=180
[badge_license]:https://img.shields.io/packagist/l/avto-dev/data-migrations-laravel.svg?longCache=true
[badge_release_date]:https://img.shields.io/github/release-date/avto-dev/data-migrations-laravel.svg?style=flat-square&maxAge=180
[badge_commits_since_release]:https://img.shields.io/github/commits-since/avto-dev/data-migrations-laravel/latest.svg?style=flat-square&maxAge=180
[badge_issues]:https://img.shields.io/github/issues/avto-dev/data-migrations-laravel.svg?style=flat-square&maxAge=180
[badge_pulls]:https://img.shields.io/github/issues-pr/avto-dev/data-migrations-laravel.svg?style=flat-square&maxAge=180
[link_releases]:https://github.com/avto-dev/data-migrations-laravel/releases
[link_packagist]:https://packagist.org/packages/avto-dev/data-migrations-laravel
[link_build_status]:https://github.com/avto-dev/data-migrations-laravel/actions
[link_coverage]:https://codecov.io/gh/avto-dev/data-migrations-laravel/
[link_changes_log]:https://github.com/avto-dev/data-migrations-laravel/blob/master/CHANGELOG.md
[link_issues]:https://github.com/avto-dev/data-migrations-laravel/issues
[link_create_issue]:https://github.com/avto-dev/data-migrations-laravel/issues/new/choose
[link_commits]:https://github.com/avto-dev/data-migrations-laravel/commits
[link_pulls]:https://github.com/avto-dev/data-migrations-laravel/pulls
[link_license]:https://github.com/avto-dev/data-migrations-laravel/blob/master/LICENSE
[getcomposer]:https://getcomposer.org/download/
