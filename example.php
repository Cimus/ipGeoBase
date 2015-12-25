<?php

/*
 * The MIT License
 *
 * Copyright 2015 Sergey Ageev (Cimus).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once 'Util/IpGeoBaseUtil.php';
require_once 'IpGeoBase.php';

use Cimus\IpGeoBase\Util\IpGeoBaseUtil;
use Cimus\IpGeoBase\IpGeoBase;

$path = __DIR__ . '/DB';

/**
 * Загружаем данные с ipgeobase.ru и конвертируем в бинарный файл
 * Данные обновляеются ежедневно, имеет смысл поставить задачу на крон
 */
//$util = new IpGeoBaseUtil();
//$util->loadArchive($path);
//$util->convertInBinary($path);



$ipGeoBase = new IpGeoBase($path);
$info =  $ipGeoBase->search('176.121.128.1');

print_r($info);

//Выводит список всех городов
//$cities = $ipGeoBase->listCity();
//
//print_r($cities);