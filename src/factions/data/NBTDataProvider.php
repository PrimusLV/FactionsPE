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

namespace factions\data;


use factions\faction\Faction;
use factions\faction\Factions;
use factions\Main;
use factions\utils\Text;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\FileWriteTask;

class NBTDataProvider
{

    private static $factionsDataFolder;
    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        self::$factionsDataFolder = $plugin->getDataFolder()."factions/";

        @mkdir(self::$factionsDataFolder);
    }

    /**
     * @param string   $name
     * @param CompoundTag $nbtTag
     * @param bool $async
     */
    public function saveFactionData($name, CompoundTag $nbtTag, $async = false)
    {
        $nbt = new NBT(NBT::BIG_ENDIAN);
        try {
            $nbt->setData($nbtTag);

            if ($async) {
                $this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new FileWriteTask(self::$factionsDataFolder . strtolower($name) . ".dat", $nbt->writeCompressed()));
            } else {
                file_put_contents(self::$factionsDataFolder . strtolower($name) . ".dat", $nbt->writeCompressed());
            }
        } catch (\Throwable $e){
            //$this->plugin->logError($e);
        }
    }

    public function loadSavedFactions()
    {
        $files = scandir(self::$factionsDataFolder);
        foreach ($files as $file) {
            if (substr($file, -4) === '.dat') {
                $nbt = $this->getOfflineFactionData(substr($file, 0, -4));
                if ($nbt) {
                    Factions::_add(new Faction($nbt, $this->plugin->getServer()));
                    if (Factions::_getFactionById($nbt->ID->getValue()) instanceof Faction) {
                        $this->plugin->getLogger()->debug(Text::get('plugin.data.faction.loaded', $nbt->Name->getValue()));
                    } else {
                        $this->plugin->getLogger()->debug(Text::get('plugin.data.faction.notLoaded', $nbt->Name->getValue()));
                    }
                }
            }
        }
    }
    
    /**
     * @param string $name
     *
     * @return CompoundTag
     */
    public function getOfflineFactionData($name) : CompoundTag {
        $name = strtolower($name);
        if(file_exists(self::$factionsDataFolder . "$name.dat")){
            try{
                $nbt = new NBT(NBT::BIG_ENDIAN);
                $nbt->readCompressed(file_get_contents(self::$factionsDataFolder . "$name.dat"));

                $nbt = $nbt->getData();
                if(!isset($nbt->Name) or $nbt->Name == "") throw new \Exception("Invalid name");

                return $nbt;
            }catch(\Throwable $e){ //zlib decode error / corrupt data
                rename(self::$factionsDataFolder . "$name.dat", self::$factionsDataFolder . "$name.dat.bak");
                $this->plugin->getLogger()->notice(Text::get('plugin.data.factionFile.corrupted', $name));
            }
        }else{
            $this->plugin->getLogger()->notice(Text::get('plugin.data.factionFile.notFound', $name));
        }
        return null;
    }

    /**
     * @param Faction $faction
     * @return bool
     */
    public function deleteFactionData(Faction $faction) : bool {
        @unlink(self::$factionsDataFolder.strtolower($faction->getName()).".dat");
        return file_exists(self::$factionsDataFolder.strtolower($faction->getName()).".dat") === false;
    }

}