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

namespace factions\event\listener;

use factions\base\ListenerBase;
use factions\integrations\Economy;
use factions\objs\FPlayer;
use factions\objs\Plots;
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

        // Test
        $logger->debug($player->getFaction()->getName() . " has " . count(Plots::_getFactionPlots($player->getFaction())) . " plots");
        Plots::_claim($player->getFaction(), $player, $player->getPlayer());
        $logger->debug($player->getFaction()->getName() . " has " . count(Plots::_getFactionPlots($player->getFaction())) . " plots");
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
