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