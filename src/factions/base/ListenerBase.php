<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 2:44 PM
 */

namespace factions\base;


use factions\Main;
use pocketmine\event\Listener;

abstract class ListenerBase implements Listener
{

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }



    public function getPlugin() : Main { return $this->plugin; }
}