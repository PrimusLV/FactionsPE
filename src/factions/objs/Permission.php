<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/22/16
 * Time: 9:06 PM
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

    public static function get() : Permission { return self::$instance; }

    public static function hasPermission(FPlayer $player, $perm){
        $rank = Text::rankToString($player->getRank());
        if(!isset(self::get()->getPermissions()[$rank])) return false;
        if(!isset(self::get()->getPermissions()[$rank][$perm])) return false;
        return self::get()->getPermissions()[$rank][$perm];
    }

    public function getPermissions() : array { return $this->permissions; }
}