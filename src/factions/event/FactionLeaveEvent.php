<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 6:02 PM
 */

namespace factions\event;


use factions\base\EventBase;
use factions\faction\Faction;
use factions\objs\FPlayer;
use pocketmine\event\Cancellable;

class FactionLeaveEvent extends EventBase implements Cancellable
{

    private $player;
    private $faction;

    public function __construct(FPlayer $player, Faction $faction){
        $this->player = $player;
        $this->faction = $faction;
    }

    public function getPlayer() : FPlayer { return $this->player; }
    public function getFaction() : Faction { return $this->faction; }

}