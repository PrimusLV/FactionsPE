<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 10:47 PM
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

}