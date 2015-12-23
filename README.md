ipGeoBase
==================
Данный модуль предназначен для определения геграфических данных по IP адресу.
Используются текстовые баз сервиса гео-локации ipgeobase.ru, которые преобразуются в бинарный формат для оптимизации поиска.

Installation
------------

### Composer

Вы можете использовать Composer для автоматизированного процесса установки:

```bash
$ php composer.phar require cimus/ip-geo-base
```

или вручную добавте ссылку в ваш файл `composer.json` и запустить` $ PHP composer.phar update`:

```json
{
    "require" : {
        "cimus/ip-geo-base": "dev-master"
    },
}
```

Usage
-----
Перед первым использованием необходимо инициализировать БД, для этого нужно запустить команды
```php
$path = __DIR__ . '/DB';
$util = new IpGeoBaseUtil();
$util->loadArchive($path);
$util->convertInBinary($path);
```

> **Note.** Данные команды можно повесить на крон и запускать с периодичность 1 раз в неделю. Данные обнавляются каждый день.

-----

Определение геграфических данных

```php
$search = new IpGeoBase($path);
$info =  $search->search('176.121.128.1');
````


