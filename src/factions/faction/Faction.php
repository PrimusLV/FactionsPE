<?php
/*
 *   88""Yb     88""Yb     88     8b    d8     88   88     .dP"Y8
 *   88__dP     88__dP     88     88b  d88     88   88     `Ybo."
 *   88"""      88"Yb      88     88YbdP88     Y8   8P     o.`Y8b
 *   88         88  Yb     88     88 YY 88     `YbodP'     8bodP'
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Latvian PHP programmer Kristaps Drivnieks (Primus)
 * @link https://Github.com/PrimusLV/FactionsPE
 */

namespace factions\faction;


use factions\event\FactionJoinEvent;
use factions\event\FactionLeaveEvent;
use factions\objs\FPlayer;
use factions\objs\Plots;
use factions\objs\Rel;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;

class Faction
{
    const MAX_PLAYERS = 10; # TODO: Configurable
    /** @var Position $home */
    protected $home;
    protected $power;

    // TODO: $leader
    protected $description;
    /** @var  string $id */
    private $id;
    /** @var array $members */
    private $members = [];
    /** @var string $name */
    private $name;
    /** @var Server $server */
    private $server;

    public function __construct(CompoundTag $nbt, Server $server) {
        $this->server = $server;
        $this->id = $nbt->ID;
        $this->name = $nbt->Name;
        $this->description = $nbt->Description;
        $this->power = $nbt->Power;
        $this->created = isset($nbt->Created) ? $nbt->Created : time();

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

        Plots::_registerFaction($this);
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
     * Add member to this faction
     * @param FPlayer $player
     * @param $rank
     * @throws \InvalidStateException
     */
    public function addMember(FPlayer $player, $rank){
        if(Factions::_getFactionFor($player->getPlayer())){
            throw new \InvalidStateException("Player is in faction");
        }
        if($rank === Rel::LEADER){ // This is handled elsewhere
            $this->newLeader($player);
            return;
        }
        $this->server->getPluginManager()->callEvent($e = new FactionJoinEvent($player, $this));

        if($e->isCancelled()) return;
        $this->members[strtolower($player->getName())] = $rank;
    }

    public function newLeader(FPlayer $member)
    {
        if ($member->getFaction() !== $this) {
            throw new \InvalidStateException("Player is not in this faction");
        }
        $this->members[$this->getLeader()] = Rel::OFFICER;
        $this->members[strtolower($member->getName())] = Rel::LEADER;
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
     * Remove member from this faction
     * @param FPlayer $player
     * @throws \InvalidStateException
     */
    public function removeMember(FPlayer $player)
    {
        if ($player->getFaction() !== $this) {
            throw new \InvalidStateException("Player isn't member of this faction");
        }
        $this->server->getPluginManager()->callEvent($e = new FactionLeaveEvent($player, $this));
        if ($e->isCancelled()) return;
        unset($this->members[strtolower($player)]);
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

    /**
     * @return float
     */
	public function getPower() : double
    {
        return (float) $this->power;
    }

    //public abstract boolean isWilderness();

    /**
     * @param string $message
     */
	public function sendMessage($message) {
        foreach($this->getOnlineMembers() as $member) {
            $member->sendMessage($message);
		}
	}

    /**
     * Returns a list of online players in the faction
     * @return Player[]
     */
    public function getOnlineMembers() : array
    {
        $o = [];
        foreach ($this->members as $member => $rank) {
            if ($p = $this->server->getPlayer($member)) $o[] = $p;
        }
        return $o;
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
            "Name" => new StringTag("Name", $this->name),
            "Created" => new IntTag("Created", $this->created)
        ]);
        if($this->home instanceof Position and $this->home->getLevel() instanceof Level){
            $nbt["HomeLevel"] = $this->home->getLevel()->getName();
            $nbt["HomeX"] = $this->home->x;
            $nbt["HomeY"] = $this->home->y;
            $nbt["HomeZ"] = $this->home->z;
        }
        return clone $nbt;
    }

    public function isFull() : bool { return count($this->members) >= self::MAX_PLAYERS; }


}