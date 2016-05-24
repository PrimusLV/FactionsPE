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

namespace factions\objs;


use factions\data\DataProvider;
use factions\event\LandChangeEvent;
use factions\faction\Faction;
use factions\faction\Factions;
use factions\Main;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class Plots {

    private static $instance = null;
    private static $plots = [];
    private static $file = "";

    /** @var Server $server */
    private static $server;

    public function __construct(Main $plugin)
    {
        if (self::$instance instanceof Plots) throw new \InvalidStateException(__CLASS__ . "already constructed");
        self::$instance = $this;
        self::$file = $plugin->getDataFolder() . "plots.json";
        self::$server = $plugin->getServer();
        $data = DataProvider::readFile(self::$file, true);
        if (($r = json_decode($data, true)) !== null) {
            self::$plots = $r;
        } else {
            self::$plots = [];
        }
    }

    public static function _registerFaction(Faction $faction)
    {
        self::get()->registerFaction($faction);
    }

    public function registerFaction(Faction $faction)
    {
        if (!isset(self::$plots[$faction->getId()])) self::$plots[$faction->getId()] = [];
    }

    public static function get()
    {
        return self::$instance;
    }

    public static function _unregisterFaction(Faction $faction)
    {
        self::get()->_unregisterFaction($faction);
    }

    public static function _getFactionPlots(Faction $faction)
    {
        return self::get()->getFactionPlots($faction);
    }

    public function getFactionPlots(Faction $faction)
    {
        return self::$plots[$faction->getId()];
    }

    public static function _claim(Faction $faction, FPlayer $player, Position $pos, $silent = false)
    {
        return self::get()->claim($faction, $player, $pos, $silent);
    }

    public function claim(Faction $faction, FPlayer $player, Position $pos, $silent = false)
    {
        if (self::_getOwnerFaction($pos) instanceof Faction === false) {
            if(!$silent){
                Server::getInstance()->getPluginManager()->callEvent($e = new LandChangeEvent($faction, $player, LandChangeEvent::CLAIM));
                if ($e->isCancelled()) return false;
            }
        } else {
            return false;
        }
        self::$plots[$faction->getId()][] = self::hash($pos);
        return true;
    }

    public static function _getOwnerFaction(Position $pos)
    {
        return self::get()->getOwnerFaction($pos);
    }

    public function getOwnerFaction(Position $pos)
    {
        return Factions::_getFactionById(self::_getOwnerId($pos));
    }

    public static function _getOwnerId(Position $pos) : string
    {
        return self::get()->getOwnerId($pos);
    }

    public function getOwnerId(Position $pos) : string
    {
        $h = self::hash($pos);
        foreach (self::$plots as $faction => $plots) {
            if (in_array($h, $plots, true)) return $faction;
        }
        return "";
    }

    public static function hash(Position $pos) : string {
        return ($pos->x >> 4).":".($pos->z >> 4).":".$pos->level->getName();
    }

    public static function _unclaim(Position $pos, $silent = false)
    {
        return self::get()->_unclaim($pos, $silent);
    }

    public static function fromHash($hash)
    {
        $d = explode(":", $hash);
        if (count($d) < 3) return false;
        if (!($level = self::$server->getLevelByName($d[2])) instanceof Level) return false;
        $x = (int)$d[0];
        $z = (int)$d[1];
        return new Position($x << 4, 0, $z << 4, $level);
    }

    public function unregisterFaction(Faction $faction)
    {
        # Call events?
        unset(self::$plots[$faction->getId()]);
    }

    public function unclaim(Position $pos, $silent = false)
    {
        if (($id = $this->_getOwnerId($pos)) !== "") {
            if (($faction = Factions::_getFactionById($id)) instanceof Faction) {
                if (!$silent) {
                    if (($player = self::$server->getPlayer($faction->getLeader())) instanceof Player) {
                        $player = FPlayer::get($player);
                    } else {
                        $player = null;
                    }
                    Server::getInstance()->getPluginManager()->callEvent($e = new LandChangeEvent($faction, $player, LandChangeEvent::UNCLAIM));
                    if ($e->isCancelled()) return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        self::$plots[$faction->getId()][] = self::hash($pos);
        return true;
    }

    public function save()
    {
        DataProvider::writeFile(self::$file, json_encode(self::$plots));
    }

}