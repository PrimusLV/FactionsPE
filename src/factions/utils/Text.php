<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 5/19/16
 * Time: 9:09 PM
 */

namespace factions\utils;


use factions\Main;

class Text
{

    const FALLBACK_LANGUAGE = "eng";
    protected static $langFolder;

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

    public function __construct(Main $plugin, $lang="eng")
    {
        if (self::$constructed) throw new \RuntimeException("Class already constructed");
        self::$instance = $this;
        $this->plugin = $plugin;
        self::$langFolder = $plugin->getDataFolder()."languages/";

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
        self::$text="";
        self::$params=[];
        return $s;
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

}