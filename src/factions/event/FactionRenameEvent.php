<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 12:40 PM
 */

namespace factions\event;


use factions\base\EventBase;
use factions\faction\Faction;
use factions\faction\Factions;
use factions\objs\FPlayer;
use pocketmine\event\Cancellable;

class FactionRenameEvent extends EventBase implements Cancellable
{

    private $factionId;
    private $renamer;
    private $newName;
    private $oldName;

    public function __construct($factionId, FPlayer $renamer, $newName, $oldName)
    {
        $this->factionId = $factionId;
        $this->renamer = $renamer;
        $this->newName = $newName;
        $this->oldName = $oldName;
    }

    public function getFaction() : Faction { return Factions::_getFactionById($this->factionId); }
    public function getFactionId() : string { return $this->factionId; }
    public function getPlayer() : FPlayer { return $this->renamer; }
    public function getNewName() : string { return $this->newName; }
    public function getOldName() : string { return $this->oldName; }

}