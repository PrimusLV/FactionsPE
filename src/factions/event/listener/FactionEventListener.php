<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 6:24 PM
 */

namespace factions\event\listener;


use factions\base\ListenerBase;
use factions\event\FactionCreateEvent;
use factions\event\FactionJoinEvent;

class FactionEventListener extends ListenerBase
{

    public function onFactionCreate(FactionCreateEvent $e){}

    public function onFactionJoin(FactionJoinEvent $e){
        $e->getPlayer()->sendMessage("You joined faction ".$e->getFaction()->getName());
    }
}