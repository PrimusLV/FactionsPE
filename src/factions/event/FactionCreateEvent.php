<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 11:49 AM
 */

namespace factions\event;


use factions\base\EventBase;
use factions\objs\FPlayer;
use pocketmine\event\Cancellable;

class FactionCreateEvent extends EventBase implements Cancellable
{

    public static $handlerList = null;

    /** @var string $factionName */
    private $factionName;
    /**
     * Creator usually is the leader of faction
     * So Faction::getLeader() === FactionCreateEvent::getCreator()
     *
     * @var FPlayer $creator
     */
    private $creator;

    /**
     * FactionCreateEvent constructor.
     * @param string $factionName
     * @param FPlayer $creator
     */
    public function __construct($factionName, FPlayer $creator)
    {
        $this->factionName = $factionName;
        $this->creator = $creator;
    }

    public function getName() : string {
        return $this->factionName;
    }

    /**
     * @return FPlayer
     */
    public function getCreator()
    {
        return $this->creator;
    }

}