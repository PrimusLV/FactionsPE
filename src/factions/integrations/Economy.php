<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 9:42 PM
 */

namespace factions\integrations;


use factions\Main;
use factions\utils\Text;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class Economy
{

    private static $instance = null;

    /** @var array $prices */
    protected $prices = [];

    /** @var mixed $economy */
    protected $economy;

    /**
     * Economy constructor.
     * @param Main $main
     * @param string $preferred
     */
    public function __construct(Main $main, $preferred="EconomyAPI")
    {
        if(self::$instance === null) self::$instance = $this;
            else return;

        $this->server = $main->getServer();
        $this->prices = $main->getConfig()->get('prices', []);
        
        $economy = ["EconomyAPI", "PocketMoney", "MassiveEconomy", "GoldStd"];
        $ec = [];
        $e="none";
        foreach($economy as $ep){
            $ins = $this->server->getPluginManager()->getPlugin($ep);
            if($ins instanceof Plugin && $ins->isEnabled()){
                $ec[$ins->getName()] = $ins;
            }
            $e=$ep;
        }
        if (isset($ec[$preferred])) {
            $this->economy = $ec[$preferred];
        } else {
            if(!empty($ec)){
                $this->economy = $ec[array_rand($e)];
            }
        }
        if($this->isLoaded()){
            $this->server->getLogger()->info(Text::get('plugin.economy.set', $this->getName()));
        } else {
            $this->server->getLogger()->info(Text::get('plugin.economy.failed'));
            $this->server->getLogger()->info(Text::get('plugin.economy.dummy'));
            $this->economy = new DummyEconomy();
        }
    }

    public static function get() : Economy { return self::$instance; }

    public function getMoney(Player $player) : int
    {
        if($this->getName() === 'EconomyAPI'){
            return $this->economy->myMoney($player);
        }
        if($this->getName() === 'PocketMoney'){
            return $this->economy->getMoney($player->getName());
        }
        if($this->getName() === 'GoldStd'){
            return $this->economy->getMoney($player); // Check
        }
        if($this->getName() === 'MassiveEconomy'){
            if ($this->economy->isPlayerRegistered($player->getName())) {
                return $this->economy->getMoney($player->getName());
            }
        }
        return 0;
    }

    public function formatMoney($amount) : string
    {
        if($this->getName() === 'EconomyAPI'){
            return $this->getMonetaryUnit() . $amount;
        }
        if($this->getName() === 'PocketMoney'){
            return $amount . ' ' . $this->getMonetaryUnit();
        }
        if($this->getName() === 'GoldStd'){
            return $amount . $this->getMonetaryUnit();
        }
        if($this->getName() === 'MassiveEconomy'){
            return $this->getMonetaryUnit() . $amount;
        }
        return $amount;
    }

    public function getMonetaryUnit() : string
    {
        if($this->getName() === 'EconomyAPI'){
            return $this->economy->getMonetaryUnit();
        }
        if($this->getName() === 'PocketMoney'){
            return 'PM';
        }
        if($this->getName() === 'GoldStd'){
            return 'G';
        }
        if($this->getName() === 'MassiveEconomy'){
            return $this->economy->getMoneySymbol() != null ? $this->economy->getMoneySymbol() : '$';
        }
        return "";
    }

    public function takeMoney(Player $player, $amount, $force=false){
        switch( strtolower($this->getName()) ){
            case 'economyapi':
                return $this->economy->reduceMoney($player, $amount, $force);
                break;
            case 'massiveeconomy':
                return $this->economy->reduceMoney($player, $amount, $force);
                break;
            case 'goldstd':
                return $this->economy->reduceMoney($player, $amount, $force);
                break;
            case 'pocketmoney':
                return $this->economy->takeMoney($player, $amount, $force);
                break;
        }
        return false;
    }

    public function getName() : string {
        if($this->economy instanceof DummyEconomy) return $this->economy->getName();
        return $this->economy->getDescription()->getName();
    }
    public function isLoaded() : bool { if($this->economy instanceof Plugin and $this->economy->isEnabled() ) return true; else return false; }
    public function getAPI() { return $this->economy; }

    public function getPrice($node) : int {
        $dirs = explode(".", $node);
        $i = 0;
        $op = $this->prices;
        while(isset($dirs[$i]) and isset($op[$dirs[$i]])){
            if(!is_array($op[$dirs[$i]])) return (int) $op[$dirs[$i]];
            $op = $op[$dirs[$i]];
            $i++;
        }
        return 0;
    }

}