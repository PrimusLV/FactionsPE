<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/19/16
 * Time: 9:00 PM
 */

namespace factions\command;


use factions\faction\Factions;
use factions\integrations\Economy;
use factions\Main;
use factions\objs\FPlayer;
use factions\utils\Text;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;

class FactionCommand extends Command implements PluginIdentifiableCommand
{
    /** @var Main */
    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;

        parent::__construct('faction', 'Main Faction command', '/faction <sub-command> [...args]', ['fac', 'f']);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     *
     * @return mixed
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if(!$this->testPermission($sender)) return true;

        if(empty($args)){
            $sender->sendMessage(Text::get('command.generic.usage'));
            return true;
        }

        if($sender instanceof Player){
            $fp = FPlayer::get($sender);
        } else {
            $sender->sendMessage(Text::get('command.not.player'));
            return true;
        }

        switch(strtolower($args[0])){
            case 'create':
            case 'cre':
            case 'new':

                // Name
                if(isset($args[1]) == false){
                    $sender->sendMessage("Enter faction name.");
                    return true;
                }
                if(strlen($args[1]) < 3 or strlen($args[1]) > 8 or !ctype_alnum($args[1])){
                    $sender->sendMessage("Invalid name");
                    return true;
                }
                $name = $args[1];
                $price = 500; // TODO: Economy::getPrice('faction.create');
                if(Economy::get()->getMoney($sender) < $price){
                    $sender->sendMessage(Text::get('economy.not.enough')); // economy.not.enough
                    return true;
                }
                if(Factions::_create($name, $fp)){
                    Economy::get()->takeMoney($sender, $price);
                    $sender->sendMessage(Text::get('command.create.success'));
                    return true;
                }
                return true;
            break;

            case 'delete':
            case 'del':
                if(!$fp->hasFaction()){
                    $sender->sendMessage(Text::get('command.not.member'));
                    return true;
                }
                if(!$fp->isLeader()){
                    $sender->sendMessage(Text::get('command.permission.not.leader'));
                    return true;
                }
                $name = $fp->getFaction()->getName();
                if(Factions::_delete($fp->getFaction())){
                    $sender->sendMessage(Text::get('command.delete.success', $name));
                } else {
                    $sender->sendMessage(Text::get('command.delete.fail'));
                }
                break;

            case 'help':
                # TODO
        }
        return false;
    }



    /**
     * @return \pocketmine\plugin\Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}