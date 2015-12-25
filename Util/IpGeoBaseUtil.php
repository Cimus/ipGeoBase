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

namespace Cimus\IpGeoBase\Util;

/**
 * @author Sergey Ageev (Cimus <s_ageev@mail.ru>)
 */
class IpGeoBaseUtil
{
    const MAX_SITY_ID_LENGTH = 4;
    private $archiveUri = 'http://ipgeobase.ru/files/db/Main/geo_files.tar.gz';
    
    
    /**
     * Загружает и распаковывает данные с ipgeobase.ru
     * 
     * @param string $path Путь до каталога с БД
     * @return boolean
     */
    public function loadArchive($path)
    {
        $path = rtrim($path, '/');
        $tmpfname = '/tmp/' . basename($this->archiveUri);
        
        copy($this->archiveUri, $tmpfname);
        
        $phar = new \PharData($tmpfname);
        $phar->extractTo($path, null, true);
        
        $cities = file_get_contents($path . '/cities.txt');
        file_put_contents($path . '/cities.txt', iconv('WINDOWS-1251', 'UTF-8', $cities));
        
        return true;
    }
    
    /**
     * Конвертирует данные в бинарное дерево
     * 
     * @param string $path Путь до каталога с БД
     * @return string файл с бинарным деревом
     */
    public function convertInBinary($path)
    {
        $path = rtrim($path, '/');
        $binaryFile = $path . '/db.bin';
        
        $cities = $this->getCities($path);
        $ipBlocks = $this->getIpBlocks($path);
        $this->normalizeIpAndCities($ipBlocks, $cities);
        
        $handle = fopen($binaryFile, 'w+');
        
        $this->packIps($ipBlocks, $handle);
        $maxLenBlockCities = $this->packCities($cities, $handle);

        $meta = [
            'countIpBlock' => count($ipBlocks),
            'countCitiesBlock' => count($cities),
            'maxLenBlockIps' => self::MAX_SITY_ID_LENGTH + 8,
            'maxLenBlockCities' => $maxLenBlockCities,
        ];
        
        fwrite($handle, str_pad(implode(chr(0), $meta), 100, ' ', STR_PAD_RIGHT));
        fclose($handle);
        
        return $binaryFile;
    }
    
    /**
     * Упаковывает гео информацию в файл
     * 
     * @param array $cities
     * @param resource $handle
     * @return int максимальная длина блока с гео информацией
     */
    private function packCities($cities, $handle)
    {
        uasort($cities, function($a, $b){
            if($a['realId'] == $b['realId']) {
                return 0;
            }
            return ($a['realId'] < $b['realId']) ? -1 : 1;
        });
        
        $maxLen = 0;
        
        foreach($cities as $item){
            $len = strlen(
                $item['country'] . chr(0) .
                $item['city'] . chr(0) . 
                $item['region'] . chr(0) . 
                $item['district'] . chr(0) . 
                $item['latitude'] . chr(0) . 
                $item['longitude'] 
                );
            if($maxLen < $len){
                $maxLen = $len;
            }
        }
        
        foreach($cities as $item){
            fwrite($handle, 
                            str_pad(
                                    $item['country'] . chr(0) .
                                    $item['city'] . chr(0) .
                                    $item['region'] . chr(0) .
                                    $item['district'] . chr(0) .
                                    $item['latitude'] . chr(0) .
                                    $item['longitude'],
                                    $maxLen, ' ', STR_PAD_RIGHT
                                    )
                    );
        }
        
        return $maxLen; 
    }

    
    /**
     * Упаковывает диапазоны IP адресов в файл
     * 
     * @param type $ipBlocks
     * @param type $handle
     * @return boolean
     */
    private function packIps($ipBlocks, $handle)
    {
        foreach($ipBlocks as $item){
            fwrite($handle, 
                $this->packIp($item['start']) . 
                $this->packIp($item['stop']) .
                $this->packSityId($item['cityId'])
            );  
        }
        return true;
    }

    /**
     * Упаковывает IP адрес (127.0.0.1) в строку
     * @param string $ip
     * @return string
     */
    private function packIp($ip)
    {
        $ip = explode('.', long2ip($ip));
        
        return chr($ip[0]) . chr($ip[1]) . chr($ip[2]) . chr($ip[3]) ;
    }
    
    /**
     * Упаковывает ID города в строку
     * @param int $id
     * @return string
     */
    private function packSityId($id)
    {
        return str_pad($id, self::MAX_SITY_ID_LENGTH, ' ', STR_PAD_RIGHT);
    }


    /**
     * Нормализует данные
     * @param array $ipBlocks
     * @param array $cities
     */
    private function normalizeIpAndCities(&$ipBlocks, &$cities)
    {
        //Удаляем города которые отсутсвуют в блоках с IP адресами
        foreach($ipBlocks as &$block){
            if(array_key_exists($block['cityId'], $cities)){
                $citiesTmp[$block['cityId']] = $cities[$block['cityId']];
            }
        }
        
        $cities = $citiesTmp;
        unset($citiesTmp);
        
        //Проставляем реальные порядковые номера блокам с адресами
        $i = 1;
        $cities[0]['realId'] = $i;
        
        foreach($cities as $id => &$item){
            if($id){
                $i++;
                $item['realId'] = $i;
            }
        }
        // ==== //
        
        
        foreach($ipBlocks as &$block){
            $cities[$block['cityId']]['country'] = $block['country'];
            $block['cityId'] = $cities[$block['cityId']]['realId'];
            unset($block['country']);
        }
        
        uasort($ipBlocks, function($a, $b){
            if($a['start'] == $b['start']) {
                return 0;
            }
            return ($a['start'] < $b['start']) ? -1 : 1;
        });
    }


    /**
     * Возвращает массив с диапазонами IP адресов
     * 
     * @param string $path
     * @return array
     * @throws \Exception
     */
    private function getIpBlocks($path)
    {
        $handle = fopen($path . '/cidr_optim.txt', 'r');
        
        $ipBlocks = [];
        
        if($handle){
            while(($buffer = fgets($handle, 4096)) !== false){
                $t = array_map('trim', explode("\t", $buffer));
                
                if($t[4] == '-')
                    $t[4] = 0;
                
                $ipBlocks[] = [
                    'start' => $t[0],
                    'stop' => $t[1],
                    'country' => $t[3],
                    'cityId' => $t[4],
                ];
                
            }
            if(!feof($handle)) {
                throw new \Exception('Error: unexpected fgets() fail');
            }
            fclose($handle);
        }
        
        return $ipBlocks;
    }

    



    /**
     * Возвращает массив городов
     * 
     * @param string $path
     * @return array
     */
    private function getCities($path)
    {
        $handle = fopen($path . '/cities.txt', 'r');
        
        $cities[0] = [
            'country' => 'unknown',
            'city' => 'unknown',
            'region' => 'unknown',
            'district' => 'unknown',
            'latitude' => 'unknown',
            'longitude' => 'unknown',
        ];
        
        if($handle){
            while(($buffer = fgets($handle, 4096)) !== false){
                $t = array_map('trim', explode("\t", $buffer));
                $cities[$t[0]] = [
                    'country' => 'unknown',
                    'city' => $t[1],
                    'region' => $t[2],
                    'district' => $t[3],
                    'latitude' => $t[4],
                    'longitude' => $t[5],
                ];
            }
            if(!feof($handle)){
                throw new \Exception('Error: unexpected fgets() fail');
            }
            fclose($handle);
        }
        return $cities;
    }
    
    
    
    
}
