<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 12:25 PM
 */

namespace factions\event;


use factions\base\EventBase;
use factions\faction\Faction;
use factions\objs\FPlayer;
use pocketmine\event\Cancellable;

class FactionJoinEvent extends EventBase implements Cancellable
{

    public static $handlerList = null;

    private $player;
    private $faction;

    public function __construct(FPlayer $player, Faction $faction)
    {
        $this->player = $player;
        $this->faction = $faction;
    }

    public function getFaction() : Faction { return $this->faction; }
    public function getPlayer() : FPlayer { return $this->player; }
    // Get previous faction? FactionJoinEvent::getPlayer()->getFaction()

}