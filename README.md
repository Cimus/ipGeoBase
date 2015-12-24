ipGeoBase
==================
Данный модуль предназначен для определения географических данных по IP адресу.
Используются текстовые базы сервиса гео-локации ipgeobase.ru, которые преобразуются в бинарный формат для оптимизации поиска.

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
        "cimus/ip-geo-base": "^1.0"
    },
}
```

Usage
-----
Перед первым использованием необходимо инициализировать БД, для этого нужно запустить команды
```php
require_once 'Util/IpGeoBaseUtil.php';

use cimus\IpGeoBase\Util\IpGeoBaseUtil;

$path = __DIR__ . '/DB';
$util = new IpGeoBaseUtil();
$util->loadArchive($path);
$util->convertInBinary($path);
```

> **Note.** Данные команды можно повесить на крон и запускать с периодичностью 1 раз в неделю. Данные обнавляются каждый день.



Определение географических данных

```php
require_once 'IpGeoBase.php';

use cimus\IpGeoBase\IpGeoBase;

$path = __DIR__ . '/DB';
$search = new IpGeoBase($path);
$info =  $search->search('176.121.128.1');

print_r($info);

Array
(
    [country] => RU
    [city] => Чебоксары
    [region] => Республика Чувашия
    [district] => Приволжский федеральный округ
    [latitude] => 56.137451
    [longitude] => 47.244030                                             
    [ip_start] => 176.121.128.0
    [ip_stop] => 176.121.191.255
)


````


