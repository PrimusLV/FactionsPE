<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/19/16
 * Time: 9:09 PM
 */

namespace factions\utils;


use factions\Main;
use factions\objs\Rel;

class Text
{

    const FALLBACK_LANGUAGE = "eng";

    /** @var string $PREFIX */
    private static $PREFIX;
    /** @var string $langFolder */
    protected static $langFolder;
    /** @var string[] $bannedWords */
    private static $bannedWords;

    /** @var Main $plugin */
    protected $plugin;
    /** @var Text $instance */
    private static $instance;

    private static $text = "";
    private static $params = [];

    private static $lang = [];
    private static $fallbackLang = [];

    /** @var bool $constructed */
    private static $constructed = false;

    private static $formats = [];

    public function __construct(Main $plugin, $lang="eng")
    {
        if (self::$constructed) throw new \RuntimeException("Class already constructed");
        self::$instance = $this;
        $this->plugin = $plugin;
        self::$langFolder = $plugin->getDataFolder()."languages/";
        self::$PREFIX = "&7[&c".$plugin->getDescription()->getName()."&7]&r";
        self::$formats = $plugin->getConfig()->get('formats', [
            "nametag" => "[{RANK}{FACTION}] {PLAYER}",
            "chat" => [
                "normal" => "[{RANK}{FACTION}] {PLAYER}: {MESSAGE}",
                "faction" => "&7F:&f [{RANK}{FACTION}] {PLAYER}: {MESSAGE}"
            ],
            "rank" => [
                "leader" => "***",
                "officer" => "**",
                "member" => "*"
            ]
        ]);
        self::$bannedWords = $plugin->getConfig()->get('banned-words', ['op', 'dump', 'gay', 'sex', 'lesbian']);

        $this->loadLang(self::$langFolder.$lang.'.ini', self::$lang);
        $this->loadLang(self::$langFolder.self::FALLBACK_LANGUAGE.'.ini', self::$fallbackLang);

        if(!empty(self::$lang)){
            $plugin->getLogger()->info(self::get('plugin.log.language.set', $lang));
        } else {
            $plugin->getLogger()->info(self::get('plugin.log.language.using.fallback', $lang, self::FALLBACK_LANGUAGE));
        }
        self::$constructed = true;
    }

    public static function get($node, ...$vars) : string {
        $text = null;
        if(isset(self::$lang[$node])) $text = self::$lang[$node];
        if($text==null and isset(self::$fallbackLang[$node])) $text = self::$fallbackLang[$node];
        if(!$text) return $node;
        self::$text = $text;
        self::$params = $vars;
        return self::$instance;
    }

    public function __toString(){
        $s = self::$text;
        $i = 0;
        foreach(self::$params as $var) {
            $s = str_replace("%var".$i, $var, $s);
            $i++;
        }
        $s = str_replace("%prefix", self::$PREFIX, $s);
        self::$text="";
        self::$params=[];
        return self::parseColorVars($s);
    }

    public static function parseColorVars($string) : STRING {
        $string = preg_replace_callback(
            "/(\\\&|\&)[0-9a-fk-or]/",
            function(array $matches){
                return str_replace("\\§", "&", str_replace("&", "§", $matches[0]));
            },
            $string
        );
        return $string;
    }

    private function loadLang($path, &$d){
        if(file_exists($path) and strlen($content = file_get_contents($path)) > 0){
            foreach(explode("\n", $content) as $line){
                $line = trim($line);
                if($line === "" or $line{0} === "#"){
                    continue;
                }
                $t = explode("=", $line, 2);
                if(count($t) < 2){
                    continue;
                }
                $key = trim($t[0]);
                $value = trim($t[1]);
                if($value === ""){
                    continue;
                }
                $d[$key] = $value;
            }
        }
    }

    // Formats

    public static function formatRank($rank){
        $rank = is_numeric($rank) ? self::rankToString($rank) : $rank;
        if(isset(self::$formats['rank'][$rank])){
            return self::$formats['rank'][$rank];
        }
        return "";
    }

    public static function rankToString($rank) : string {
        if(!is_numeric((int) $rank)) return "";
        switch($rank){
            case Rel::LEADER: return "leader"; break;
            case Rel::OFFICER: return "officer"; break;
            case Rel::MEMBER: return 'member'; break;
            default: return ""; break;
        }
    }

    public static function getFormat($format) : string {
        $dirs = explode(".", $format);
        $i = 0;
        $op = self::$formats;
        while(isset($dirs[$i]) and isset($op[$dirs[$i]])){
            if(!is_array($op[$dirs[$i]])) return self::parseColorVars($op[$dirs[$i]]);
            $op = $op[$dirs[$i]];
            $i++;
        }
        return $format;
    }

    public static function isNameBanned($name) : bool {
        foreach(self::$bannedWords as $word){
            if(strpos(strtolower($name), strtolower($word)) !== false) return true;
        }
        return false;
    }

}