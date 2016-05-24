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

use factions\data\DataProvider;
use factions\event\FactionCreateEvent;
use factions\objs\FPlayer;
use factions\objs\Plots;
use factions\objs\Rel;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;

class Factions
{
    /** @var Factions $instance */
    private static $instance;

    /**
     * Stores all Faction objects
     * @var \SplObjectStorage
     */
    protected $storage;

    public function __construct(){
        self::$instance = $this;
        $this->storage = new \SplObjectStorage;
    }

    public static function _getFactionById($id) {
        return self::get()->getFactionById($id);
    }

    //////////////////////
    //  Static methods  //
    //////////////////////

	/**
     * Get Faction by their ID
     *
     * @param string $id
     * @return Faction|null
     */
	public function getFactionById($id){
        foreach($this->storage as $faction){
            if($faction->getId() === $id) return $faction;
        }
        return null;
    }

    public static function get()
    {
        return self::$instance;
    }

    public static function _getFactionAt(Position $pos)
    {
        return self::get()->getFactionAt($pos);
    }

	/**
     * Get Faction at a location
     * @param Position $pos
     * @return Faction|null
     */
	public function getFactionAt(Position $pos){
        # TODO
    }

    public static function _getFactionFor(Player $player)
    {
        return self::get()->getFactionFor($player);
    }

	/**
     * Get Faction for a player
     * @param Player $player
     * @return Faction
     */
	public function getFactionFor(Player $player){
        foreach($this->storage as $faction){
            if(array_key_exists(strtolower(strtolower($player->getName())), $faction->getMembers())) return $faction;
        }
        return null;
    }

    public static function _delete(Faction $faction) : bool
    {
        return self::get()->delete($faction);
    }

    public function delete(Faction $faction) : bool {
        if(DataProvider::get()->deleteFactionData($faction)){
            $this->storage->detach($faction);
            Plots::_unregisterFaction($faction);
            return $this->storage->contains($faction) === false;
        }
        return false;
    }

    public static function _create($name, FPlayer $creator, $members = [], $power = 0, $description = "", $home = null)
    {
        return self::get()->create($name, $creator, $members, $power, $description, $home);
    }

    public function create($name, FPlayer $creator, $members=[], $power = 0, $description = "", $home = null){
        if(Factions::_getFactionByName($name) instanceof Faction) return false;
        if(Factions::_getFactionForMember($creator) instanceof Faction) return false;

        $mems = [];
        foreach($members as $member => $rank){
            if($member == "") continue;
            if(!is_numeric($rank)) continue;

            $mems[strtolower($member)] = new CompoundTag(strtolower($member), [
                "Name" => new StringTag("Name", $member),
                "Rank" => new ByteTag("Rank", $rank),
            ]);
        }
        $mems[strtolower($creator->getName())] = new CompoundTag(strtolower($creator->getName()), [
            "Name" => new StringTag("Name", strtolower($creator->getName())),
            "Rank" => new ByteTag("Rank", Rel::LEADER)
        ]);

        $nbt = new CompoundTag($name, [
            "ID" => new StringTag("ID", self::generateId()),
            "Members" => new ListTag("Members", $mems),
            "Power" => new IntTag("Power", $power),
            "Description" => new StringTag("Description", $description),
            "Name" => new StringTag("Name", $name)
        ]);
        if($home instanceof Position and $home->getLevel() instanceof Level){
            $nbt["HomeLevel"] = $home->getLevel()->getName();
            $nbt["HomeX"] = $home->x;
            $nbt["HomeY"] = $home->y;
            $nbt["HomeZ"] = $home->z;
        }

        Server::getInstance()->getPluginManager()->callEvent($e = new FactionCreateEvent($name, $creator));
        if($e->isCancelled()) return false;

        $faction = new Faction($nbt, Server::getInstance());

        return Factions::_add($faction);
    }

    public static function _getFactionByName($name)
    {
        return self::get()->getFactionByName($name);
    }

    /**
     * Get faction by their name
     * @param $name
     * @return Faction|null
     */
    public function getFactionByName($name)
    {
        foreach ($this->storage as $faction) {
            if (strtolower($faction->getName()) === strtolower($name)) return $faction;
        }
        return null;
    }

    public static function _getFactionForMember(FPlayer $player)
    {
        return self::get()->getFactionFor($player->getPlayer());
    }

    public static function generateId()
    {
        return substr(md5(base64_encode(mt_rand(PHP_INT_MIN, PHP_INT_MAX))), 0, 24);
    }

    // Check if factions is enabled

    public static function _add(Faction $faction) : bool
    {
        return self::get()->add($faction);
    }

    public function add(Faction $faction) : bool
    {
        if ($this->storage->contains($faction)) return false;
        $this->storage->attach($faction);
        return $this->storage->contains($faction);
    }

    /**
     * Get Faction for a sender
     * @param CommandSender $sender
     * @return Faction|null
     */
    public function getFactionForSender(CommandSender $sender)
    {
        if ($sender instanceof Player) return $this->getFactionFor($sender);

        return null;
    }

    public function isFactionsEnabled(Level $level) : bool
    {
        # TODO
    }

    public function getAll() : array {
        $f = [];
        foreach($this->storage as $faction){
            $f[] = $faction;
        }
        return $f;
    }

	// Get Wilderness ID
	//public abstract String getWildernessId();

	// Get Safezone ID
	//public abstract String getSafeZoneId();

	// Get WarZone ID
	//public abstract String getWarZoneId();




}