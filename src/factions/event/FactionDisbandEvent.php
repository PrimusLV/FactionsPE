<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 12:05 PM
 */

namespace factions\event;


use factions\base\EventBase;
use factions\faction\Faction;
use factions\faction\Factions;
use factions\objs\FPlayer;
use pocketmine\event\Cancellable;

class FactionDisbandEvent extends EventBase implements Cancellable
{

    private $factionId;
    private $player;

    public function __construct($factionId, FPlayer $player)
    {
        $this->factionId = $factionId;
        $this->player = $player;
    }

    /**
     * @return FPlayer
     */
    public function getPlayer() : FPlayer { return $this->player; }
    public function getFactionId() : string { return $this->factionId; }
    public function getFaction() : Faction { return Factions::get()->getFactionById($this->factionId); }


}