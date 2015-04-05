<?php

namespace Minifixio\onevsone;

use Minifixio\onevsone\model\Arena;
use Minifixio\onevsone\utils\PluginUtils;
use Minifixio\onevsone\model\SignRefreshTask;
use Minifixio\onevsone\OneVsOne;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\utils\Config;
use pocketmine\tile\Sign;

/**
 * Manages PVP arenas
 */
class ArenaManager{

	/** @var Arena[] **/
	private $arenas = array();
	
	/** @var Player[] **/
	private $queue = array();	
	
	/** @var Config **/
	private $config;
	
	/** @var Tiles[] **/
	private $signTiles = array();

	const SIGN_REFRESH_DELAY = 5;
	private $signRefreshTaskHandler;
	
	/**
	 * Init the arenas
	 */
	public function init($arenaPositions, $config){
		PluginUtils::logOnConsole("Init ArenaManager");
		$this->parsePositions($arenaPositions);
		$this->config = $config;
		
		// Launch sign refreshing task
		$task = new SignRefreshTask(OneVsOne::getInstance());
		$task->arenaManager = $this;
		$this->signRefreshTaskHandler = Server::getInstance()->getScheduler()->scheduleRepeatingTask($task, self::SIGN_REFRESH_DELAY * 20);
				
	}
	
	/**
	 * Create arenas
	 */
	public function parsePositions(array $arenaPositions) {
		foreach ($arenaPositions as $n => $arenaPosition) {
			Server::getInstance()->loadLevel($arenaPosition[3]);
			if(($level = Server::getInstance()->getLevelByName($arenaPosition[3])) === null){
				Server::getInstance()->getLogger()->error($arenaPosition[3] . " is not loaded. Arena " . $n . " is disabled.");
			}
			else{
				$newArenaPosition = new Position($arenaPosition[0], $arenaPosition[1], $arenaPosition[2], $level);
				$newArena = new Arena($newArenaPosition);
				array_push($this->arenas, $newArena);
				Server::getInstance()->getLogger()->debug("Arena " . $n . " loaded at position " . $newArenaPosition->__toString());
			}
		}
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
		
		// Check that player is not currently in an arena
		$currentArena = $this->getPlayerArena($newPlayer);
		if($currentArena != null){
			$newPlayer->sendMessage(" ");
			$newPlayer->sendMessage("[1vs1] Vous etes deja dans une arene");
			$newPlayer->sendMessage(" ");				
			return;
		}
		
		// add player to queue
		PluginUtils::logOnConsole("Adding " . $newPlayer->getName() . " to queue");
		array_push($this->queue, $newPlayer);
		
		// display some stats
		PluginUtils::logOnConsole("There is actually " . count($this->queue) . " players in the queue");
		$newPlayer->sendMessage("[1vs1] Vous avez rejoins la file d'attente.");
		$newPlayer->sendMessage("[1vs1] Il y a " . count($this->queue) . " joueurs en attente.");
		$newPlayer->sendMessage("[1vs1] Il faut minimum 2 joueurs pour commencer un duel.");
		
		$this->launchNewRounds();
		$this->refreshSigns();
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
	 * Get current arena for player
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
	
	/**
	 * Reference a new arena at this location
	 * @param Location $location for the new Arena
	 */
	public function referenceNewArena(Location $location){
		// Create a new arena
		$newArena = new Arena($location);	
		
		// Add it to the array
		array_push($this->arenas,$newArena);
		
		// Save it to config
		$this->config->set(count($this->arenas), [$newArena->position->getX(), $newArena->position->getY(), $newArena->position->getZ(), $newArena->position->getLevel()->getName()]);
		$this->config->save();		
	}
	
	/**
	 * Remove a player from queue 
	 */
	public function removePlayerFromQueueOrArena(Player $player){
		$currentArena = $this->getPlayerArena($player);
		if($currentArena != null){
			$currentArena->onPlayerDeath($player);
			return;
		}
		
		$index = array_search($player, $this->queue);
		if($index != -1){
			unset($this->queue[$index]);
		}
		$this->refreshSigns();
	}
	
	public function getNumberOfArenas(){
		return count($this->arenas);
	}
	
	public function getNumberOfFreeArenas(){
		$numberOfFreeArenas = count($this->arenas);
		foreach ($this->arenas as $arena){
			if($arena->active){
				$numberOfFreeArenas--;
			}
		}
		return $numberOfFreeArenas;
	}	
	
	public function getNumberOfPlayersInQueue(){
		return count($this->queue);
	}
	
	/**
	 * Add a new 1vs1 sign
	 */
	public function addSign(Sign $signTile){
		array_push($this->signTiles, $signTile);
	}
	
	/**
	 * Refresh all 1vs1 signs
	 */
	public function refreshSigns(){
		foreach ($this->signTiles as $signTile){
			$signTile->setText('[1vs1]', "-En attente: " . $this->getNumberOfPlayersInQueue(), "-Arenes: " . $this->getNumberOfFreeArenas(), "-+===+-");
		}
	}
}



