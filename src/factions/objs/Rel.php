<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/18/16
 * Time: 10:51 PM
 */

namespace factions\objs;


use factions\faction\Faction;

final class Rel
{
    const LEADER = 0;
    const OFFICER = 1;
    const MEMBER = 2;
    const RECRUIT = 3;
    const ALLY = 4;
    const TRUCE = 5;
    const NEUTRAL = 6;
    const ENEMY = 7;

    // How to save these fucking relationships?
    // array (
    //  Faction (
    //      Faction2 => self::ALLY
    //  )
    // )

    public static function getRelationship(Faction $faction1, Faction $faction2)
    {
        # TODO
        return self::NEUTRAL;
    }
}