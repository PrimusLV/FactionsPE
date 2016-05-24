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

namespace factions\objs;


use factions\Main;
use factions\utils\Text;

class Permission {

    /** @var Permission|null $instance */
    private static $instance = null;

    protected $permissions = [ # TODO: add inherit
        "leader" => [
            "invite" => true,
            "claim" => true,
            "unclaim" => true,
            "sethome" => true,
            "motd" => true,
            "kick" => true,
            "info" => true,
            "home" => true
        ],
        "member" => [
            "info" => true,
            "home" => true,
        ],
        "officer" => [
            "invite" => true,
            "claim" => true,
            "unclaim" => true,
            "info" => true,
            "home" => true
        ]
    ];

    public function __construct(Main $plugin)
    {
        if(self::$instance === null) self::$instance = $this;
        else return;

        $this->permissions = $plugin->getConfig()->get('permissions', $this->permissions);
    }

    public static function hasPermission(FPlayer $player, $perm){
        $rank = Text::rankToString($player->getRank());
        if(!isset(self::get()->getPermissions()[$rank])) return false;
        if(!isset(self::get()->getPermissions()[$rank][$perm])) return false;
        return self::get()->getPermissions()[$rank][$perm];
    }

    public function getPermissions() : array { return $this->permissions; }

    public static function get() : Permission
    {
        return self::$instance;
    }
}