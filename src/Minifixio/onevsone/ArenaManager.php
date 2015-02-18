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
		$spawnPosition = Server::getInstance()->getDefaultLevel()->getSpawnLocation();
		$firstArena = new Arena($spawnPosition);
		array_push($this->arenas,$firstArena);
	}
	
	/**
	 * Add player into the queue
	 */
	public function addNewPlayerToQueue(Player $newPlayer){
		
		// Check that player is not already in the queue
		if(in_array($newPlayer, $this->queue)){
			$newPlayer->sendMessage(" ");
			$newPlayer->sendMessage("[1vs1] Vous etes deja dans la file d'attente.");
			$newPlayer->sendMessage(" ");
			return;
		}
		
		// add player to queue
		PluginUtils::logOnConsole("Adding " . $newPlayer->getName() . " to queue");
		array_push($this->queue, $newPlayer);
		
		// display some stats
		PluginUtils::logOnConsole("There is actually " . count($this->queue) . " players in the queue");
		$newPlayer->sendMessage("[1vs1] Vous avez rejoins la file d'attente.");
		$newPlayer->sendMessage(" ");
		$newPlayer->sendMessage("[1vs1] Il y a " . count($this->queue) . " joueurs en attente.");
		
		$this->launchNewRounds();
	}

	/**
	 * Launches new rounds if necessary
	 */
	private function launchNewRounds(){
		
		// Check that there is at least 2 players in the queue
		if(count($this->queue) < 2){
			PluginUtils::logOnConsole("There is not enought players in the queue.");
			return;
		}
		
		// Check if there is any arena free (not active)
		$arena = $this->arenas[0];
		while ($arena !== FALSE && $arena->active) {
			$arena = next($this->arenas);
		}
		if($arena == FALSE){
			PluginUtils::logOnConsole("There are no free arenas." );
			return;
		}
		
		// Send the players into the arena (and remove them from queues)
		$roundPlayers = array();
		array_push($roundPlayers, array_shift($this->queue), array_shift($this->queue));
		PluginUtils::logOnConsole("" . implode($roundPlayers));
		$arena->startRound($roundPlayers);
	}
	
	/**
	 * Gat current arena for player
	 * @param Player $player
	 * @return Arena or NULL
	 */
	public function getPlayerArena(Player $player){
		foreach ($this->arenas as $arena) {
			if($arena->isPlayerInArena($player)){
				return $arena;
			}
		}	
		return NULL;	
	}
}



