<?php
namespace factions;

use factions\command\FactionCommand;
use factions\data\DataProvider;
use factions\data\NBTDataProvider;
use factions\event\listener\FactionEventListener;
use factions\event\listener\PlayerEventListener;
use factions\faction\Faction;
use factions\faction\Factions;
use factions\integrations\Economy;
use factions\objs\Permission;
use factions\utils\Text;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    /** @var Factions $factions */
    protected $factions;
    /** @var Text $text */
    protected $text;
    /** @var NBTDataProvider $data */
    protected $data;
    /** @var Economy $economy */
    protected $economy;
    /** @var Permission $perm */

    public function onLoad()
    {
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder()."logs");
        @mkdir($this->getDataFolder()."languages");
        $this->saveDefaultConfig();
    }

    public function onEnable(){

            $this->text = new Text($this, $this->getConfig()->get('language', Text::FALLBACK_LANGUAGE));
            $this->economy = new Economy($this);
            $this->factions = new Factions();
            $this->data = new DataProvider($this, 'NBT');
            $this->perm = new Permission($this);

            # Load saved factions
            DataProvider::get()->loadSavedFactions();
            # Register command
            $this->getServer()->getCommandMap()->register('factions', new FactionCommand($this));
            # Run managers... ?

            # Register Listeners
            foreach ([PlayerEventListener::class, FactionEventListener::class] as $listener) {
                $this->getServer()->getPluginManager()->registerEvents(new $listener($this), $this);
            }

            $this->getLogger()->info(Text::get('plugin.log.enable'));
    }

    public function onDisable()
    {
        foreach(Factions::get()->getAll() as $faction){
            /** @var Faction $faction */
            DataProvider::get()->saveFactionData($faction->getName(), $faction->getNBT());
        }
        $this->getLogger()->info(Text::get('plugin.log.disable'));
    }

}