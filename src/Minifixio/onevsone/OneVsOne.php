<?php

namespace Minifixio\onevsone;

use pocketmine\plugin\PluginBase;

use Minifixio\onevsone\ArenaManager;
use Minifixio\onevsone\EventsManager;
use Minifixio\onevsone\utils\PluginUtils;
use Minifixio\onevsone\command\JoinCommand;

class OneVsOne extends PluginBase{
	
	/** @var ArenaManager */
	private $arenaManager;
	
	/**
	* Plugin is enabled by PocketMine server
	*/
    public function onEnable(){
    	
    	PluginUtils::logOnConsole("Init OneVsOne plugin");
    	
    	$this->arenaManager = new ArenaManager();
    	$this->arenaManager->init();
    	
    	// Register events
    	$this->getServer()->getPluginManager()->registerEvents(
    			new EventsManager($this->arenaManager), 
    			$this
    		);
    	
    	// Register commands
    	$command = new JoinCommand($this, $this->arenaManager);
    	$this->getServer()->getCommandMap()->register("joinpvp", $command);
    }
    
    public function onDisable() {
 
    }
}