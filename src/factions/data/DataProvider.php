<?php
/*
 *   88""Yb     88""Yb     88     8b    d8     88   88     .dP"Y8
 *   88__dP     88__dP     88     88b  d88     88   88     `Ybo."
 *   88"""      88"Yb      88     88YbdP88     Y8   8P     o.`Y8b
 *   88         88  Yb     88     88 YY 88     `YbodP'     8bodP'
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Latvian PHP programmer Kristaps Drivnieks (Primus)
 * @link https://Github.com/PrimusLV/FactionsPE
 */

namespace factions\data;


use factions\Main;

class DataProvider
{

    /** @var object $instance */
    private static $instance;

    public function __construct(Main $plugin, $type)
    {
        switch(strtolower($type)){
            case 'nbt':
                self::$instance = new NBTDataProvider($plugin);
                break;
            default:
                throw new \RuntimeException("No valid data provider loaded.");
                break;
        }
    }

    public static function get(){ return self::$instance; }

    public static function readFile($file, $create = false) : string
    {
        if (!file_exists($file)) {
            if ($create === true)
                file_put_contents($file, "");
            else
                return "";
        }
        return file_get_contents($file);
    }

    public static function writeFile($file, $data){
        $f = fopen($file, "w");
        if($f){
            fwrite($f, $data);
        }
        fclose($f);
    }

}