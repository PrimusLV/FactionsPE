<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/19/04
 * Time: 12:48 AM
 */

namespace factions\objs;


use factions\data\DataProvider;
use factions\event\LandChangeEvent;
use factions\faction\Faction;
use factions\faction\Factions;
use factions\Main;
use pocketmine\level\Position;
use pocketmine\Server;

class Plots {

    private static $plots = [];

    public static  function init(Main $plugin){
        $rdata = DataProvider::readFile($plugin->getDataFolder()."plots.json");
        if( ($r = json_decode($rdata)) !== null ){
            self::$plots = $r;
        } else {
            self::$plots = $r;
        }
    }

    /**
     * @return string ID of Faction
     */
    public static function getOwnerId(Position $pos) : string {
        $h = self::hash($pos);
        foreach(self::$plots as $faction => $plots){
            if(in_array($h, $plots, true)) return $faction;
        }
        return "";
    }

    public static function getOwnerFaction(Position $pos){
        return Factions::_getFactionById(self::getOwnerId($pos));
    }

    public static function getFactionPlots(Faction $faction){
        return self::$plots[$faction->getId()];
    }

    public static function claim(Faction $faction, FPlayer $player, Position $pos, $silent=false){
        if(self::getOwnerFaction($pos) instanceof Faction === false){
            if(!$silent){
                Server::getInstance()->getPluginManager()->callEvent($e = new LandChangeEvent($faction, $player, LandChangeEvent::CLAIM));
            }
        }
        return false;
    }
    public static function unclaim(Position $pos, $silent=false){

    }

    public static function hash(Position $pos) : string {
        return ($pos->x >> 4).":".($pos->z >> 4).":".$pos->level->getName();
    }


}