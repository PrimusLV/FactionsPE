<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 4:41 PM
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
            $this->plugin->logError($e);
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
                $this->plugin->logError($e);
            }
        }else{
            $this->plugin->getLogger()->notice(Text::get('plugin.data.factionFile.notFound', $name));
        }
        return null;
    }
    
    public function loadSavedFactions(){
        $files = scandir(self::$factionsDataFolder);
        foreach($files as $file){
            if(substr($file, -4) === '.dat'){
                $nbt = $this->getOfflineFactionData(substr($file, 0, -4));
                if($nbt){
                    Factions::_add(new Faction($nbt, $this->plugin->getServer()));
                    if(Factions::_getFactionById($nbt->ID->getValue()) instanceof Faction){
                        var_dump(Factions::_getFactionByName($nbt->Name->getValue()));
                        $this->plugin->getLogger()->debug(Text::get('plugin.data.faction.loaded', $nbt->Name->getValue()));
                    } else {
                        $this->plugin->getLogger()->debug(Text::get('plugin.data.faction.notLoaded', $nbt->Name->getValue()));
                    }
                }
            }
        }
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