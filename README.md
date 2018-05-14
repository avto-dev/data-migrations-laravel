<p align="center">
  <img src="https://laravel.com/assets/img/components/logo-laravel.svg" alt="Laravel" width="240" />
</p>

# Миграция данных БД для Laravel

[![Version][badge_version]][link_packagist]
[![Build Status][badge_build_status]][link_build_status]
[![StyleCI][badge_styleci]][link_styleci]
[![Coverage][badge_coverage]][link_coverage]
[![Code Quality][badge_quality]][link_coverage]
[![Issues][badge_issues]][link_issues]
[![License][badge_license]][link_license]
[![Downloads count][badge_downloads_count]][link_packagist]

Данный пакет добавляет в ваше Laravel-приложение функционал мигрирования данных БД.

## Установка

Для установки данного пакета выполните в терминале следующую команду:

```shell
$ composer require avto-dev/data-migrations-laravel "^1.0"
```

> Для этого необходим установленный `composer`. Для его установки перейдите по [данной ссылке][getcomposer].

> Обратите внимание на то, что необходимо фиксировать мажорную версию устанавливаемого пакета.

Если вы используете Laravel версии 5.5 и выше, то сервис-провайдер данного пакета будет зарегистрирован автоматически. В противном случае вам необходимо самостоятельно зарегистрировать сервис-провайдер в секции `providers` файла `./config/app.php`:

```php
'providers' => [
    // ...
    AvtoDev\DataMigrationsLaravel\DataMigrationsServiceProvider::class,
]
```

Опубликуйте конфигурационный файл, при помощи которого вы можете переопределить имя таблицы в БД для хранения данных о миграциях, имя соединения и прочие настройки:

```shell
$ ./artisan vendor:publish --provider="AvtoDev\DataMigrationsLaravel\DataMigrationsServiceProvider"
```

После чего отредактируйте файл `./config/data-migrations.php` на своё усмотрение и завершите установку, выполнив команду:

```shell
./artisan data-migrate:install
```

## Использование

Проблема, которую решает данный пакет - это отсутствие встроенного в Laravel механизма мигрирования "боевых" данных в ваше приложение (`seeds` это механизм заполнения фейковыми данными изначально, а миграции БД несут ответственность за схему и т.д., но _не_ данные). 

Для того, что бы лучше ознакомиться с "механикой" работы данного пакета рассмотрим следующую ситуацию - ваше приложение использует 2 подключения к различным БД (`default` и `second_db`), и вам необходимо в каждом из них в уже существующие таблицы добавить какие-либо данные.

Для этого вам необходимо выполнить 2 команды:

```shell
./artisan make:data-migration "Add some data into first table"
./artisan make:data-migration --connection="second_db" "Add some data into second table"
```

После выполнения которых создадутся 2 файла:

- `./storage/data_migrations/2018_01_01_022000_add_some_data_into_first_table.sql`
- `./storage/data_migrations/second_db/2018_01_01_022001_add_some_data_into_second_table.sql`

Которые вы можете наполнить SQL-командами, производящими необходимые insert-ы и так далее (помните, что идеологически верно использовать их **только** для манипуляций с данными).

После этого вам достаточно выполнить:

```shell
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

### Тестирование

Для тестирования данного пакета используется фреймворк `phpunit`. Для запуска тестов выполните в терминале:

```shell
$ git clone git@github.com:avto-dev/data-migrations-laravel.git ./data-migrations-laravel && cd $_
$ composer install
$ composer test
```

## Поддержка и развитие

Если у вас возникли какие-либо проблемы по работе с данным пакетом, пожалуйста, создайте соответствующий `issue` в данном репозитории.

Если вы способны самостоятельно реализовать тот функционал, что вам необходим - создайте PR с соответствующими изменениями. Крайне желательно сопровождать PR соответствующими тестами, фиксирующими работу ваших изменений. После проверки и принятия изменений будет опубликована новая минорная версия.

## Лицензирование

Код данного пакета распространяется под лицензией [MIT][link_license].

[badge_version]:https://img.shields.io/packagist/v/avto-dev/data-migrations-laravel.svg?style=flat&maxAge=30
[badge_downloads_count]:https://img.shields.io/packagist/dt/avto-dev/data-migrations-laravel.svg?style=flat&maxAge=30
[badge_license]:https://img.shields.io/packagist/l/avto-dev/data-migrations-laravel.svg?style=flat&maxAge=30
[badge_build_status]:https://scrutinizer-ci.com/g/avto-dev/data-migrations-laravel/badges/build.png?b=master
[badge_styleci]:https://styleci.io/repos/132609297/shield
[badge_coverage]:https://scrutinizer-ci.com/g/avto-dev/data-migrations-laravel/badges/coverage.png?b=master
[badge_quality]:https://scrutinizer-ci.com/g/avto-dev/data-migrations-laravel/badges/quality-score.png?b=master
[badge_issues]:https://img.shields.io/github/issues/avto-dev/data-migrations-laravel.svg?style=flat&maxAge=30
[link_packagist]:https://packagist.org/packages/avto-dev/data-migrations-laravel
[link_styleci]:https://styleci.io/repos/132609297/
[link_license]:https://github.com/avto-dev/data-migrations-laravel/blob/master/LICENSE
[link_build_status]:https://scrutinizer-ci.com/g/avto-dev/data-migrations-laravel/build-status/master
[link_coverage]:https://scrutinizer-ci.com/g/avto-dev/data-migrations-laravel/?branch=master
[link_issues]:https://github.com/avto-dev/data-migrations-laravel/issues
[getcomposer]:https://getcomposer.org/download/
