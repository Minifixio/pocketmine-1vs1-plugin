<?php

namespace Minifixio\onevsone;

use Minifixio\onevsone\model\Arena;
use Minifixio\onevsone\utils\PluginUtils;

use pocketmine\Server;
use pocketmine\Player;

/**
 * Manages PVP arenas
 */
class ArenaManager{

	/** @var Arena[] **/
	private $arenas = array();
	
	/** @var Player[] **/
	private $queue = array();	
	
	/**
	 * Init the arenas
	 */
	public function init(){
		PluginUtils::logOnConsole("Init ArenaManager");
		$firstArena = new Arena(0,0,0,Server::getInstance()->getDefaultLevel());
		$arenas[0] = $firstArena;
	}
	
	/**
	 * Add player into the queue
	 */
	public function addNewPlayerToQueue(Player $newPlayer){
		
		// Check that player is not already in the queue
		// TODO : for Minifixio :)
		
		// add player to queue
		PluginUtils::logOnConsole("Adding " . $newPlayer->getName() . " to queue");
		array_push($this->queue, $newPlayer);
		
		// display some stats
		PluginUtils::logOnConsole("There is actually " . count($this->queue) . " players in the queue");
		
		$this->launchNewRounds();
	}

	/**
	 * Launches new rounds if necessary
	 */
	private function launchNewRounds(){
		
		// Check that there is at least 2 players in the queue
		// TODO : for Minifixio :)
		
		// Check if there is any arena free (not active)
		// TODO : for Minifixio :)		
		
		// Send the players into the arena (and remove them from queues)
		// TODO : for Minifixio :)		
		
	}
}



