<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/20/16
 * Time: 2:43 PM
 */

namespace factions\event\listener;

use factions\base\ListenerBase;
use factions\integrations\Economy;
use factions\objs\FPlayer;
use factions\objs\Rel;
use factions\utils\Text;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;

class PlayerEventListener extends ListenerBase
{

    public function onPlayerPreLogin(PlayerPreLoginEvent $e){
        FPlayer::get($e->getPlayer());
    }

    /**
     * @param PlayerJoinEvent $e
     * @priority MONITOR
     */
    public function onPlayerJoin(PlayerJoinEvent $e){
        $player = FPlayer::get($e->getPlayer());
        FPlayer::updatePlayerTag($player);

        // Debug
        $logger = $this->getPlugin()->getLogger();
        $logger->debug("Player ".$player->getName()." joined.");
        $logger->debug( ($player->hasFaction()) ? "He is member of faction ".$player->getFaction()->getName() : "He is not member of any faction" );
        if($player->hasFaction()){
            $logger->debug("Rank: ".Text::rankToString($player->getRank()));
            $logger->debug("Money: ".Economy::get()->getMoney($player->getPlayer()));
        }
    }

    public function onPlayerRespawn(PlayerRespawnEvent $e){
        FPlayer::updatePlayerTag($e->getPlayer());
    }


    /**
     * @param PlayerChatEvent $e
     * @priority LOWEST
     * @ignoreCancelled false
     */
    public function onPlayerChat(PlayerChatEvent $e){
        if ($e->isCancelled()) return;
        $fplayer = FPlayer::get($e->getPlayer());
        if($fplayer->hasFaction()){
            $format = $fplayer->getChatChannel() === FPlayer::CHAT_FACTION ? "chat.faction" : "chat.normal";
            $f = Text::getFormat($format);
            $f = str_replace(["{RANK}", "{FACTION}", "{MESSAGE}", "{PLAYER}"], [
                Text::formatRank($fplayer->getRank()),
                $fplayer->getFaction()->getName(),
                $e->getMessage(),
                $fplayer->getPlayer()->getDisplayName()
            ], $f);
            $e->setFormat($f);
            $fplayer->sendMessageToChannel($f);
            if($format === "chat.faction") $e->setRecipients($fplayer->getFaction()->getOnlineMembers());
        }
    }

    public function onEntityDamage(EntityDamageEvent $e){
        if($e instanceof EntityDamageByEntityEvent === false) return;
        /** @var EntityDamageByEntityEvent $e */
        $victim = $e->getEntity();
        $attacker = $e->getDamager();

        if($victim instanceof Player and $attacker instanceof Player){

            $fvictim = FPlayer::get($victim);
            $fattacker = FPlayer::get($attacker);

            if($fvictim->hasFaction() === false or $fattacker === false) return; # I don't give a shit what happens next xD

            if($fvictim->getFaction() === $fattacker->getFaction()){
                $attacker->sendMessage("You can't hurt your function mate.");
                $e->setCancelled(true);
                return;
            }
            switch(Rel::getRelationship($fattacker->getFaction(), $fvictim->getFaction())){
                case Rel::ALLY:
                    $attacker->sendPopup("Your faction is in ally with his faction.");
                    # CANCEL ?
                    return; break;
                case Rel::ENEMY:
                    $attacker->sendPopup("Your and his faction are enemies, attack!");
                    return; break;
                default:
                    return; break;
            }
        }

    }

}
