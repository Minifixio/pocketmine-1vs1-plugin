<?php

namespace Minifixio\onevsone;

use pocketmine\plugin\PluginBase;

use Minifixio\onevsone\ArenaManager;
use Minifixio\onevsone\EventsManager;
use Minifixio\onevsone\utils\PluginUtils;
use Minifixio\onevsone\command\JoinCommand;
use Minifixio\onevsone\command\ReferenceArenaCommand;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Server;


class OneVsOne extends PluginBase{
	
	/** @var OneVsOne */
	private static $instance;
	
	/** @var ArenaManager */
	private $arenaManager;
	
	/** @var Config */
	public $arenaConfig;
	
	public $messages;
	
	CONST SIGN_TITLE = '[1vs1]';
	
	/**
	* Plugin is enabled by PocketMine server
	*/
    public function onEnable(){
    	self::$instance = $this;
    	PluginUtils::logOnConsole(TextFormat::GREEN . "Init OneVsOne plugin");
    	
    	// Get arena positions from arenas.yml
    	@mkdir($this->getDataFolder());
    	$this->arenaConfig = new Config($this->getDataFolder()."config.yml", Config::YAML, array());    	

    	// Load custom messages
    	$this->saveResource("messages.yml");
    	$this->messages = new Config($this->getDataFolder() ."messages.yml");
    	
    	$this->arenaManager = new ArenaManager();
    	$this->arenaManager->init($this->arenaConfig);
    	
    	// Register events
    	$this->getServer()->getPluginManager()->registerEvents(
    			new EventsManager($this->arenaManager), 
    			$this
    		);
    	
    	// Register commands
    	$joinCommand = new JoinCommand($this, $this->arenaManager);
    	$this->getServer()->getCommandMap()->register($joinCommand->commandName, $joinCommand);
    	
    	$referenceArenaCommand = new ReferenceArenaCommand($this, $this->arenaManager);
    	$this->getServer()->getCommandMap()->register($referenceArenaCommand->commandName, $referenceArenaCommand);    	
    }
    
    public static function getInstance(){
    	return self::$instance;
    }
    
    public static function getMessage($key){
    	return str_replace("&", "§", self::$instance->messages->get($key));
    }
    
    public function onDisable() {
 
    }

}
