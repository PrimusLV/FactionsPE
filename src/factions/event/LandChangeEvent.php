<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 12:57 PM
 */

namespace factions\event;


use factions\base\EventBase;
use factions\faction\Faction;
use factions\faction\Factions;
use factions\objs\FPlayer;
use pocketmine\event\Cancellable;

class LandChangeEvent extends EventBase implements Cancellable
{

    const CLAIM = 1;
    const UNCLAIM = 2;

    private $factionId;
    private $player;
    private $changeType;

    public function __construct($factionId, FPlayer $player, $changeType)
    {
        $this->factionId = $factionId;
        $this->changeType = $changeType;
        $this->player = $player;
    }

    public function getFaction() : Faction { return Factions::_getFactionById($this->factionId); }
    public function getFactionId() : string { return $this->factionId; }
    public function getPlayer() : FPlayer { return $this->player; }
    public function getChangeType() : int { return $this->changeType; }

}