<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/18/16
 * Time: 10:52 PM
 */

namespace factions\objs;


use factions\faction\Factions;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\UUID;

class FPlayer
{
    /** @var bool */
    protected $isConsole = false;
    /**
     * Holds all FPlayer objects
     * @var array
     */
    private static $fplayerMap = null;

    public function __construct(Player $player, $isConsole = false){
        $this->isConsole = $isConsole;

        if( !$isConsole ) {
            $this->player = $player;
        }
    }


    /**
     * Get FPlayer from Player
     * @param Player $player
     * @return FPlayer
     */
    public static function get(Player $player) : FPlayer
    {
        if( !isset(self::$fplayerMap[$player->getUniqueId()->toString()]) ) self::$fplayerMap[$player->getUniqueId()->toString()] = $player;
        return self::$fplayerMap[$player->getUniqueId()->toString()] = new FPlayer($player);
    }


    /**
     * Returns player owner of this class, or null if it's Console
     * @return Player|null
     */
    public function getPlayer(){
        return $this->player;
    }

    /**
     * Get player's faction
     * @return \factions\faction\Faction
     */
    public function getFaction(){
        # TODO: isConsole
        return Factions::_getFactionFor($this->player);
    }

    /**
     * Check if owner is class is in faction or not
     * @return bool
     */
    public function hasFaction() : bool {
        if($this->getFaction() == null) return false;
        if($this->getFaction()->getId() == "none") return false;
        if($this->getFaction()->isWilderness()) return false;
        return true;
    }

    /**
     * Get whether this class owner is ranked as leader
     * @return bool
     */
    public function isLeader() : bool {
        if( $this->hasFaction() ) return $this->getFaction()->getLeader() === strtolower($this->getName());
        return false;
    }

    /**
     * Get whether this class owner is ranked as officer
     * @return bool
     */
    public function isOfficer() : bool {
        if( $this->hasFaction() ) return in_array($this, $this->getFaction()->getOfficers(), true);
        return false;
    }

    public function getName() : string {
        if ($this->isConsole) return "@console";
        return $this->player->getName();
    }

    public function getUUID() : UUID {
        # TODO: return console UUID
        if($this->player instanceof Player) return $this->player->getUniqueId();
        return null;
    }

    public function getLevel() : Level {
        return $this->player->getLevel();
    }

    /**
     * Get Faction ID
     * @return string
     */
    public function getFactionId() : string {
        if( $this->hasFaction() ) return $this->getFaction()->getId();
        return "";
    }


    // Tasks
        # TODO
    // Tasks

    /**
     * Get Player position
     * @return null|\pocketmine\level\Position
     */
    public function getPosition(){
        if($this->getPlayer()) return $this->player->getPosition();
        return null;
    }

    /**
     * Get whether this class owner is online
     * @return bool
     */
    public function isOnline() : bool {
        if($this->isConsole) return true;

        if($this->player instanceof Player) return $this->player->isOnline();
        return false;
    }

    public function teleport(Vector3 $pos){
        if($this->player instanceof Player) $this->player->teleport($pos);
    }

    public function sendMessage($message)
    {
        if($this->player instanceof Player) $this->player->sendMessage($message);
        (new ConsoleCommandSender())->sendMessage($message);
    }

    public function getRank() : int
    {
        if($this->isLeader()) return Rel::LEADER;
        if($this->isOfficer()) return Rel::OFFICER;
        return Rel::MEMBER;
    }


}
