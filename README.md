# FactionsPE

FactionsPE is plugin faction plugin for PocketMine-MP.

> Made in hope, I could make money.

## Installation

1. Download **.phar** file from latest release
2. Drop it into **~/plugins** folder
3. Start server

After first run the data folder and all necessary resources for it will be generated.

#### Multi-Language

You can change language from **config.yml**. Supported languages

* English (DEFAULT)
* Latvian

## For developers

Plugin was developed in such manner that you don't have to get main class of the plugin. Most of classes holds self instances which 
main class constructed and can be accessed throughout the code.

#### API Examples

###### Creating new Faction
```php
Factions::_create($name, FPlayer $creator)
```

###### Getting Faction and FPlayer instances
Faction:
```php
Factions::_getFactionByName($name)
Factions::_getFactionById($name)
Factions::_getFactionFor(Player $player)
```
FPlayer:
```php
FPlayer::get(Player $player)
```
