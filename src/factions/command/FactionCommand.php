<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/19/16
 * Time: 9:00 PM
 */

namespace factions\command;


use factions\faction\Faction;
use factions\faction\Factions;
use factions\integrations\Economy;
use factions\Main;
use factions\objs\FPlayer;
use factions\objs\Permission;
use factions\objs\Rel;
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

            /////////////////////////////// CREATE ///////////////////////////////

            case 'create':
            case 'cre':
            case 'new':

                if(!isset($args[1])) {
                    $sender->sendMessage("Usage: /f create <faction name>");
                    return true;
                }
                if(Economy::get()->getPrice('faction.create') > Economy::get()->getMoney($sender)){
                    $sender->sendMessage("You don't have enough money.");
                    return true;
                }
                if(FPlayer::get($sender)->hasFaction()) {
                    $sender->sendMessage("You must leave this faction first");
                    return true;
                }
                if(!(ctype_alnum($args[1]))) {
                    $sender->sendMessage("You may only use letters and numbers!");
                    return true;
                }
                if(Text::isNameBanned($args[1])) {
                    $sender->sendMessage("This name is not allowed.");
                    return true;
                }
                if(Factions::_getFactionByName($args[1])) {
                    $sender->sendMessage("Faction with that name already exists");
                    return true;
                }
                if(strlen($args[1]) > $this->plugin->getConfig()->get("MaxFactionNameLength", 8)) {
                    $sender->sendMessage("This name is too long. Please try again!");
                    return true;
                }
                    $name = $args[1];

                    Factions::_create($name, FPlayer::get($sender));
                    FPlayer::updatePlayerTag($sender);

                    $sender->sendMessage("Faction successfully created!");
                    return true;
                break;

            /////////////////////////////// DELETE ///////////////////////////////
            
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
                break;

            /////////////////////////////// INVITE ///////////////////////////////

            case 'invite':

                $fplayer = FPlayer::get($sender);
                if(!isset($args[1])) {
                    $sender->sendMessage("Usage: /f invite <player>");
                    return true;
                }
                if(!$fplayer->hasFaction()) {
                    $sender->sendMessage("You must be in a faction to use this");
                    return true;
                }
                if(!Permission::get()->hasPermission($fplayer, "invite")) {
                    $sender->sendMessage("You do not have permission to do this");
                    return true;
                }
                if( $fplayer->getFaction()->isFull()  ) {
                    $sender->sendMessage("Faction is full. Please kick players to make room.");
                    return true;
                }
                $invited = $this->plugin->getServer()->getPlayerExact($args[1]);
                if(!$invited instanceof Player) {
                    $sender->sendMessage("Player not online!");
                    return true;
                }
                if( FPlayer::get($invited)->hasFaction() ) {
                    $sender->sendMessage("Player is currently in a faction");
                    return true;
                }

                if(FPlayer::get($invited)->invite($fplayer->getFaction(), $fplayer)) {

                    $sender->sendMessage($invited->getDisplayName() . " has been invited!");
                    $invited->sendMessage("You have been invited to {$fplayer->getFaction()->getName()} faction by {$fplayer->getPlayer()->getDisplayName()}. Type '/f accept' or '/f deny' into chat to accept or deny!");
                    var_dump(FPlayer::get($invited));
                    $this->test = FPlayer::get($invited);
                    return true;
                } else {
                    $sender->sendMessage("Failed to send invitation, please try again later.");
                }
                break;

            /////////////////////////////// ACCEPT ///////////////////////////////

            case 'accept':

                $fplayer = FPlayer::get($sender);
                $inv = $fplayer->getInvitation();
                var_dump($fplayer);
                var_dump($this->test === $fplayer);

                if( empty($inv)) {
                    $sender->sendMessage("You have not been invited to any factions!");
                    return true;
                }
                if( (time() - $inv['received']) >= 30) {
                    $sender->sendMessage("You don't have any active invitation.");
                    return true;
                }
                /** @var Faction[]|mixed[] $inv */
                if( $inv["to"]->isFull() ){
                    $sender->sendMessage("Sorry but there is no free place left.");
                    return true;
                }
                if( $fplayer->acceptInvitation() ) {
                    $sender->sendMessage("You successfully joined {$fplayer->getFaction()->getName()}!");
                    /** @var FPlayer[]|mixed[] $inv */
                    $inv["by"]->sendMessage($sender->getDisplayName()." accepted your invitation.");
                    return true;
                } else {
                    $sender->sendMessage("Oops, something went wrong. Try again later.");
                    return true;
                }
                break;

            /////////////////////////////// DENY ///////////////////////////////


            case 'deny':

                $fplayer = FPlayer::get($sender);

                if( empty($fplayer->getInvitation())) {
                    $sender->sendMessage("You have not been invited to any factions!");
                    //return true;
                }
                $inv = $fplayer->getInvitation();
                if( (time() - $inv['received']) >= 30) {
                    $sender->sendMessage("You don't have any active invitation.");
                    return true;
                }
                if( $fplayer->denyInvitation() ) {
                    $sender->sendMessage("You denied invitation to join {$fplayer->getFaction()->getName()}!");
                    /** @var FPlayer[]|mixed[] $inv */
                    $inv["by"]->sendMessage($sender->getDisplayName()." denied your invitation.");
                    return true;
                } else {
                    $sender->sendMessage("Oops, something went wrong. Try again later.");
                    return true;
                }
                break;

            /////////////////////////////// LEADER ///////////////////////////////

                case 'leader':

                    if(!isset($args[1])) {
                        $sender->sendMessage("Usage: /f leader <player>");
                        return true;
                    }
                    $fplayer = FPlayer::get($sender);
                    if(!$fplayer->hasFaction()) {
                        $sender->sendMessage("You must be in a faction to use this!");
                        return true;
                    }
                    if(!$fplayer->isLeader()) {
                        $sender->sendMessage("You must be leader to use this");
                        return true;
                    }
                    if(!($target = $this->plugin->getServer()->getPlayerExact($args[1])) instanceof Player) {
                        $sender->sendMessage("Player not online!");
                        return true;
                    }
                    $ftarget = FPlayer::get($target);
                    if($ftarget->getFaction() !== $fplayer->getFaction()) {
                        $sender->sendMessage("Add player to your faction first!");
                        return true;
                    }

                    $sender->sendMessage("You are no longer leader!");
                    $this->plugin->getServer()->getPlayer($args[1])->sendMessage("You are now leader of {$ftarget->getFaction()->getName()}!");
                    return true;
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