<?php
namespace factions\objs;


use factions\faction\Faction;
use factions\faction\Factions;
use factions\utils\Text;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\UUID;

class FPlayer
{

    const CHAT_GLOBAL = 1;
    const CHAT_LEVEL = 2;
    const CHAT_FACTION = 3;
    const CHAT_NOT_FACTION = 4;
    const CHAT_NORMAL = 5;

    protected $invitation = [];
    /** @var bool */
    protected $isConsole = false;
    /**
     * Holds all FPlayer objects
     * @var array
     */
    private static $fplayerMap = null;
    protected $chatChannel = self::CHAT_GLOBAL;

    public function __construct(Player $player, $isConsole = false){
        $this->isConsole = $isConsole;

        if( !$isConsole ) {
            $this->player = $player;
        }
    }


    /**
     * Get FPlayer from Player
     * @param Player $player
     * @return FPlayer
     */
    public static function get(Player $player) : FPlayer
    {
        if( !isset(self::$fplayerMap[$player->getUniqueId()->toString()]) ) self::$fplayerMap[$player->getUniqueId()->toString()] = new FPlayer($player);
        return self::$fplayerMap[$player->getUniqueId()->toString()];
    }
    public static function getAll() : array { return self::$fplayerMap; }

    public static function updatePlayerTag($players=[]){
        if($players instanceof FPlayer or $players instanceof Player)
            $players = [$players];
        elseif (empty($players))
            $players = self::$fplayerMap;
        
        foreach($players as $player){
            $player = $player instanceof FPlayer ? $player : self::get($player);
            $tag = Text::getFormat('nametag');
            if($player->hasFaction()) {
                $tag = str_replace(["{RANK}", "{FACTION}", "{PLAYER}"], [
                    Text::formatRank($player->getRank()),
                    $player->getFaction()->getName(),
                    $player->getPlayer()->getDisplayName()
                ], $tag);
                $player->getPlayer()->setNameTag($tag);
            } else {
                $player->getPlayer()->setNameTag($player->getPlayer()->getDisplayName());
            }
        }
    }


    /**
     * Returns player owner of this class, or null if it's Console
     * @return Player|null
     */
    public function getPlayer(){
        return $this->player;
    }

    /**
     * Get player's faction
     * @return \factions\faction\Faction
     */
    public function getFaction(){
        # TODO: isConsole
        return Factions::_getFactionFor($this->player);
    }

    /**
     * Check if owner is class is in faction or not
     * @return bool
     */
    public function hasFaction() : bool {
        if($this->getFaction() == null) return false;
        if($this->getFaction()->getId() == "none") return false;
        if($this->getFaction()->isWilderness()) return false;
        return true;
    }

    /**
     * Get whether this class owner is ranked as leader
     * @return bool
     */
    public function isLeader() : bool {
        if( $this->hasFaction() ) return $this->getFaction()->getLeader() === strtolower($this->getName());
        return false;
    }

    /**
     * Get whether this class owner is ranked as officer
     * @return bool
     */
    public function isOfficer() : bool {
        if( $this->hasFaction() ) return in_array($this, $this->getFaction()->getOfficers(), true);
        return false;
    }

    public function isMember() : bool {
        if($this->getRank() === Rel::MEMBER) return true;
        return false;
    }

    public function getName() : string {
        if ($this->isConsole) return "@console";
        return $this->player->getName();
    }

    public function getUUID() : UUID {
        # TODO: return console UUID
        if($this->player instanceof Player) return $this->player->getUniqueId();
        return null;
    }

    public function getLevel() : Level {
        return $this->player->getLevel();
    }

    /**
     * Get Faction ID
     * @return string
     */
    public function getFactionId() : string {
        if( $this->hasFaction() ) return $this->getFaction()->getId();
        return "";
    }


    // Tasks
        # TODO
    // Tasks

    /**
     * Get Player position
     * @return null|\pocketmine\level\Position
     */
    public function getPosition(){
        if($this->getPlayer()) return $this->player->getPosition();
        return null;
    }

    /**
     * Get whether this class owner is online
     * @return bool
     */
    public function isOnline() : bool {
        if($this->isConsole) return true;

        if($this->player instanceof Player) return $this->player->isOnline();
        return false;
    }

    public function teleport(Vector3 $pos){
        if($this->player instanceof Player) $this->player->teleport($pos);
    }

    public function sendMessage($message)
    {
        if($this->player instanceof Player) $this->player->sendMessage($message);
        else (new ConsoleCommandSender())->sendMessage($message);
    }

    public function getRank() : int
    {
        if($this->isLeader()) return Rel::LEADER;
        if($this->isOfficer()) return Rel::OFFICER;
        return Rel::MEMBER;
    }

    ////////////////////////////// INVITATION ///////////////////////////////////////
    public function invite(Faction $faction, FPlayer $player, $rank = Rel::MEMBER) : bool {
        $this->invitation = [
            "to" => $faction,
            "by" => $player,
            "received" => time(),
            "rank" => $rank
        ];
        var_dump($this->invitation);
        var_dump($this->getInvitation());
        return empty($this->invitation) === false; // For debug purposes
    }
    public function acceptInvitation() : bool {
        echo "INVITATION ACCEPT";
        if(empty($this->invitation)) return false;
        $inv = $this->invitation;
        if( !isset($inv["to"]) or !isset($inv["by"]) or !isset($inv["received"]) or !isset($inv["rank"])){
            $this->invitation = [];
            return false;
        }
        if( $inv["to"]->isFull() ) return false;
        if( (time() - $inv["received"]) > 30 ) return false; # Timed out/Expired

        $inv["to"]->addMember($this, $inv["rank"]);
        $this->invitation = [];
        return true;
    }
    public function getInvitation() : array { return $this->invitation; }
    public function denyInvitation(){ $this->invitation=[]; return true; }

    ////////////////////////////// CHAT CHANNELS ///////////////////////////////////////
    public function getChatChannel() : int { return $this->chatChannel; }
    public function setChatChannel($channel){ $this->chatChannel = $channel; }
    public function sendMessageToChannel($message){
        foreach(self::$fplayerMap as $player){
            if($player->getChatChannel() === $this->getChatChannel()){
                $player->sendMessage($message);
            }
        }
    }


}
