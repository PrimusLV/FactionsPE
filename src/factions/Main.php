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
use factions\utils\Text;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;

class Main extends PluginBase {

    /** @var Factions $factions */
    protected $factions;
    /** @var Text $text */
    protected $text;
    /** @var NBTDataProvider $data */
    protected $data;
    /** @var Economy $economy */
    protected $economy;

    public function onLoad()
    {
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder()."logs");
        @mkdir($this->getDataFolder()."languages");
        $this->saveDefaultConfig();
        $this->text = new Text($this, $this->getConfig()->get('language', 'eng'));
    }

    public function onEnable(){
        $this->economy = new Economy($this->getServer());

        $this->factions = new Factions();
        # Load data provider
        $this->data = new DataProvider($this, 'NBT');
        # Load saved factions
        DataProvider::get()->loadSavedFactions();

        # Register command
        $this->getServer()->getCommandMap()->register('factions', new FactionCommand($this));
        # Run managers... ?

        # Register Listeners
        foreach([PlayerEventListener::class, FactionEventListener::class] as $listener){
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


    public function logError(\Throwable $e){

            $f = date("G-i-s").".errorlog";
            $file = fopen($this->getDataFolder()."logs/".$f, "w");
            $server = $this->getServer();


            $this->getLogger()->info(Text::get('plugin.log.error', $this->getDataFolder()."logs/$f"));

            $output = "";
            $output .= "----------------------------------------\n";
            $output .= "Error Log started on ".date("Y.m.d G:i:s")."\n";
            $output .= "----------------------------------------\n";
            $output .= "Server: ".$server->getName()." ".$server->getVersion()." ".$server->getCodename()." ({$server->getApiVersion()})\n";
            $output .= "Plugin: ".$this->getFullName()." for API ";
                foreach($this->getDescription()->getCompatibleApis() as $api){
                    $output .= "$api ";
                }
            rtrim($output);
            $output .= "\nMachine: ".Utils::getOS()."\n";


			$output .= "----------------------------------------\n";
			$output .= "Error:".$e->getMessage()."\n";
            $output .= "Traceback: ".$e->getTraceAsString()."\n";
            $output .= "----------------------------------------\n";

            $output .= "------------- Error  Dump --------------\n\n\n";
            $output .= "\n\n\n".base64_decode($output);
            fwrite($file, $output);

            foreach($server->getOnlinePlayers() as $p){
                if($p->isOp()) $p->sendMessage(TextFormat::RED."An internal error occurred in ".$this->getName()." plugin. Check your console.");
            }
            fclose($file);
    }
}