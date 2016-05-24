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
use factions\objs\Plots;
use factions\utils\Text;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

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
    protected $perm;
    /** @var Plots $plots */
    protected $plots;

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
        $this->plots = new Plots($this);

            # Load saved factions
            DataProvider::get()->loadSavedFactions();
            # Register command
            $this->getServer()->getCommandMap()->register('factions', new FactionCommand($this));
            # Run managers... ?

            # Register Listeners
            foreach ([PlayerEventListener::class, FactionEventListener::class] as $listener) {
                $this->getServer()->getPluginManager()->registerEvents(new $listener($this), $this);
            }

        $credit = base64_decode("Cjg4IiJZYiAgICAgODgiIlliICAgICA4OCAgICAgOGIgICAgZDggICAgIDg4ICAgODggICAgIC5kUCJZOAo4OF9fZFAgICAgIDg4X19kUCAgICAgODggICAgIDg4YiAgZDg4ICAgICA4OCAgIDg4ICAgICBgWWJvLiIKODgiIiIgICAgICA4OCJZYiAgICAgIDg4ICAgICA4OFliZFA4OCAgICAgWTggICA4UCAgICAgby5gWThiCjg4ICAgICAgICAgODggIFliICAgICA4OCAgICAgODggWVkgODggICAgIGBZYm9kUCcgICAgIDhib2RQJwo=");
        foreach (explode("\n", $credit) as $line) {
            $this->getLogger()->info(TextFormat::DARK_PURPLE . "   " . $line);
        }
            $this->getLogger()->info(Text::get('plugin.log.enable'));
    }

    public function onDisable()
    {
        foreach(Factions::get()->getAll() as $faction){
            /** @var Faction $faction */
            DataProvider::get()->saveFactionData($faction->getName(), $faction->getNBT());
        }
        if (isset($this->plots) and $this->plots instanceof Plots) $this->plots->save();
        
        $this->getLogger()->info(Text::get('plugin.log.disable'));
    }

}