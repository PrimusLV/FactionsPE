<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/18/16
 * Time: 10:22 PM
 */

namespace factions\faction;


use factions\event\FactionJoinEvent;
use factions\event\FactionLeaveEvent;
use factions\objs\FPlayer;
use factions\objs\Rel;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;

class Faction
{

    /** @var  string $id */
    private $id;
    /** @var array $members */
    private $members = [];

    // TODO: $leader

    /** @var string $name */
    private $name;

    /** @var Position $home */
    protected $home;
    protected $power;
    protected $description;

    /** @var Server $server */
    private $server;

    public function __construct(CompoundTag $nbt, Server $server) {
        $this->server = $server;
        $this->id = $nbt->ID;
        $this->name = $nbt->Name;
        $this->description = $nbt->Description;
        $this->power = $nbt->Power;

        var_dump($nbt);
        if(isset($nbt->HomeLevel) and isset($nbt->HomeX) and isset($nbt->homeY) and isset($nbt->homeZ)){
            $level = $server->getLevelByName($nbt->HomeLevel);
            if($level instanceof Level){
                $this->home = new Position($nbt->HomeX, $nbt->HomeY, $nbt->HomeZ, $level);
            } else {
                $this->home = null;
            }
        }

        foreach($nbt->Members->getValue() as $member){
            /** @var CompoundTag $member */
            $this->members[$member->Name->getValue()] = $member->Rank->getValue();
        }


    }

    /**
     * Returns the ID of the Faction
     * @return string
     */
    public function getId() : string { return $this->id; }

    /**
     * Returns faction name
     * @return string
     */
    public function getName() : string { return $this->name; }

	/**
     * Returns a list of players in the Faction
     * @return FPlayer[]
     */
    public function getMembers() : array
    {
        return $this->members;
	}

    /**
     * Returns a list of online players in the faction
     * @return Player[]
     */
    public function getOnlinePlayers() : array {
        $o = [];
        foreach($this->members as $member){
            if($p = $this->server->getPlayer($member)) $o[] = $p;
        }
        return $o;
    }

    /**
     * Add member to this faction
     * @param FPlayer $player
     * @param $rank
     * @throws \InvalidStateExcpetion
     */
    public function addMember(FPlayer $player, $rank){
        if(Factions::_getFactionFor($player->getPlayer())){
            throw new \InvalidStateExcpetion("Player is in faction");
        }
        $this->server->getPluginManager()->callEvent($e = new FactionJoinEvent($player, $this));

        if($e->isCancelled()) return;
        $this->members[strtolower($player->getName())] = $rank;
    }

    /**
     * Remove member from this faction
     * @param FPlayer $player
     * @throws \InvalidStateException
     */
    public function removeMember(FPlayer $player){
        if($player->getFaction() !== $this){
            throw new \InvalidStateException("Player isn't member of this faction");
        }
        $this->server->getPluginManager()->callEvent($e = new FactionLeaveEvent($player, $this));
        if($e->isCancelled()) return;
        unset($this->members[strtolower($player)]);
    }

    /**
     * Gets the leader of the Faction
     * @return string
     */
	public function getLeader() : string {
        foreach($this->members as $m => $rank){
            if($rank === Rel::LEADER) return $m;
        }
        return "";
    }

    /**
     * Returns a list of the officers in a Faction
     * @return array
     */
    public function getOfficers() : array {
        $officers = [];
        foreach($this->members as $member => $rank){
            if($rank === Rel::OFFICER) $officers[] = $member;
        }
        return $officers;
    }

    /**
     * Gets the Home location of the Faction
     * @return null|Position
     */
    public function getHome(){
        return $this->home;
    }

    public function setHome(Position $pos){
        // TODO: FactionSetHomeEvent
        if(Factions::_getFactionAt($pos) === $this) {
            $this->home = $pos;
            return true;
        }
        return false;
    }

	/**
     * Gets the description of the Faction
     * @return string
     */
	public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * Returns a list of enemies
     * @return array
     */
	public function getEnemies() : array
    {
        # TODO
    }

    /**
     * Returns a list of allies
     * @return array|Faction [
     */
    public function getAllies() : array
    {
        # TODO
    }

    /**
     * Determines if a Faction is an enemy
     * @param Faction $faction
     * @return bool
     */
	public function isEnemyOf(Faction $faction) : bool {
        # TODO
    }

    /**
     * Determines if a Faction is an ally
     * @param Faction $faction
     * @return bool
     */
	public function isAllyOf(Faction $faction) : bool {
        # TODO
    }

	/**
     * Returns how much the Faction has claimed
     * @return int
     */
	public function getLandCount() : int
    {
        # TODO
    }


    /**
     * Get relation status with other faction
     * @param Faction $faction
     * @return Rel
     */
	public function getRelationshipTo(Faction $faction) : int {
        return Rel::getRelationship($this, $faction);
    }

	//public abstract boolean isWilderness();

    /**
     * @return float
     */
	public function getPower() : double
    {
        return (float) $this->power;
    }

    /**
     * @param string $message
     */
	public function sendMessage($message) {
        foreach($this->getOnlineMembers() as $member) {
            $member->sendMessage($message);
		}
	}

    public function isWilderness() : bool {
        #TODO
        return false;
    }

    public function getNBT() : CompoundTag {
        $mems = [];
        foreach($this->members as $member => $rank){
            if($member == "") continue;
            if(!is_numeric($rank)) continue;

            $mems[$member] = new CompoundTag($member, [
                "Name" => new StringTag("Name", $member),
                "Rank" => new ByteTag("Rank", $rank),
            ]);
        }

        $nbt = new CompoundTag($this->name, [
            "ID" => new StringTag("ID", $this->id),
            "Members" => new ListTag("Members", $mems),
            "Power" => new ByteTag("Power", $this->power),
            "Description" => new StringTag("Description", $this->description),
            "Name" => new StringTag("Name", $this->name)
        ]);
        if($this->home instanceof Position and $this->home->getLevel() instanceof Level){
            $nbt["HomeLevel"] = $this->home->getLevel()->getName();
            $nbt["HomeX"] = $this->home->x;
            $nbt["HomeY"] = $this->home->y;
            $nbt["HomeZ"] = $this->home->z;
        }
        return clone $nbt;
    }


}