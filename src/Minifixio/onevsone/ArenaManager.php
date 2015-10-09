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
use pocketmine\utils\TextFormat;

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
	public function init(Config $config){
		PluginUtils::logOnConsole("- Init ArenaManager");
		$this->config = $config;
		
		if(!$this->config->arenas){
			$this->config->set('arenas', []);
			$arenaPositions = [];
		}
		else{
			$arenaPositions = $this->config->arenas;
		}
		
		if(!$this->config->signs){
			$this->config->set('signs', []);
			$signPositions = [];
		}
		else{
			$signPositions = $this->config->signs;
		}	

		// Load arenas and signs
		$this->parseArenaPositions($arenaPositions);
		$this->parseSignPositions($signPositions);
		
		// Launch sign refreshing task
		$task = new SignRefreshTask(OneVsOne::getInstance());
		$task->arenaManager = $this;
		$this->signRefreshTaskHandler = Server::getInstance()->getScheduler()->scheduleRepeatingTask($task, self::SIGN_REFRESH_DELAY * 20);
	}
	
	/**
	 * Create arenas
	 */
	public function parseArenaPositions(array $arenaPositions) {
		foreach ($arenaPositions as $n => $arenaPosition) {
			Server::getInstance()->loadLevel($arenaPosition[3]);
			if(($level = Server::getInstance()->getLevelByName($arenaPosition[3])) === null){
				Server::getInstance()->getLogger()->error("[1vs1] - " . $arenaPosition[3] . " is not loaded. Arena " . $n . " is disabled.");
			}
			else{
				$newArenaPosition = new Position($arenaPosition[0], $arenaPosition[1], $arenaPosition[2], $level);
				$newArena = new Arena($newArenaPosition, $this);
				array_push($this->arenas, $newArena);
				Server::getInstance()->getLogger()->debug("[1vs1] - Arena " . $n . " loaded at position " . $newArenaPosition->__toString());
			}
		}
	}	

	/**
	 * Load signs
	 */
	public function parseSignPositions(array $signPositions) {
		PluginUtils::logOnConsole("[1vs1] - Load signs... " . count($signPositions) . " signs");
		foreach ($signPositions as $n => $signPosition) {
			Server::getInstance()->loadLevel($signPosition[3]);
			if(($level = Server::getInstance()->getLevelByName($signPosition[3])) !== null){
				$newSignPosition = new Position($signPosition[0], $signPosition[1], $signPosition[2], $level);
				$tile = $level->getTile($newSignPosition);
				if($tile != null){
					$cleanTileTitle = TextFormat::clean($tile->getText()[0]);
					$cleanOnevsOneTitle = TextFormat::clean(OneVsOne::SIGN_TITLE);
					
					// Load it only if it's a sign with OneVsOne title
					if($tile !== null && $tile instanceof Sign && $cleanTileTitle === $cleanOnevsOneTitle){
						array_push($this->signTiles, $tile);
						continue;
					}
				}
			}
			else{
				PluginUtils::logOnConsole("[1vs1] - Level " . $signPosition[3] . " does not exists. Please check configuration." );
			}
		}
	}	
	
	/**
	 * Add player into the queue
	 */
	public function addNewPlayerToQueue(Player $newPlayer){
		
		// Check that player is not already in the queue
		if(in_array($newPlayer, $this->queue)){
			PluginUtils::sendDefaultMessage($newPlayer, OneVsOne::getMessage("queue_alreadyinqueue"));
			return;
		}
		
		// Check that player is not currently in an arena
		$currentArena = $this->getPlayerArena($newPlayer);
		if($currentArena != null){
			PluginUtils::sendDefaultMessage($newPlayer, OneVsOne::getMessage("arena_alreadyinarena"));
			return;
		}
		
		// add player to queue
		array_push($this->queue, $newPlayer);
		
		// display some stats
		PluginUtils::logOnConsole("[1vs1] - There is actually " . count($this->queue) . " players in the queue");
		PluginUtils::sendDefaultMessage($newPlayer, OneVsOne::getMessage("queue_join"));
		PluginUtils::sendDefaultMessage($newPlayer, OneVsOne::getMessage("queue_playersinqueue"). count($this->queue));
		$newPlayer->sendTip(OneVsOne::getMessage("queue_popup"));
		
		$this->launchNewRounds();
		$this->refreshSigns();
	}

	/**
	 * Launches new rounds if necessary
	 */
	private function launchNewRounds(){
		
		// Check that there is at least 2 players in the queue
		if(count($this->queue) < 2){
			Server::getInstance()->getLogger()->debug("There is not enough players to start a duel : " . count($this->queue));
			return;
		}
		
		// Check if there is any arena free (not active)
		Server::getInstance()->getLogger()->debug("Check ".  count($this->arenas) . " arenas");
		
		$freeArena = NULL;
		foreach ($this->arenas as $arena){
			if(!$arena->active){
				$freeArena = $arena;
				break;
			}
		}
		
		if($freeArena == NULL){
			Server::getInstance()->getLogger()->debug("[1vs1] - No free arena found");
			return;
		}
		
		// Send the players into the arena (and remove them from queues)
		$roundPlayers = array();
		array_push($roundPlayers, array_shift($this->queue), array_shift($this->queue));
		Server::getInstance()->getLogger()->debug("[1vs1] - Starting duel : " . $roundPlayers[0]->getName() . " vs " . $roundPlayers[1]->getName());
		$freeArena->startRound($roundPlayers);
	}
	
	/**
	 * Allows to be notify when round ends
	 * @param Arena $arena
	 */
	public function notifyEndOfRound(Arena $arena){
		$this->launchNewRounds();
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
		$newArena = new Arena($location, $this);	
		
		// Add it to the array
		array_push($this->arenas,$newArena);
		
		// Save it to config
		$arenas = $this->config->arenas;
		array_push($arenas, [$newArena->position->getX(), $newArena->position->getY(), $newArena->position->getZ(), $newArena->position->getLevel()->getName()]);
		$this->config->set("arenas", $arenas);
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
		$signs = $this->config->signs;
		$signs[count($this->signTiles)] = [$signTile->getX(), $signTile->getY(), $signTile->getZ(), $signTile->getLevel()->getName()];
		$this->config->set("signs", $signs);
		$this->config->save();
		array_push($this->signTiles, $signTile);
	}
	
	/**
	 * Refresh all 1vs1 signs
	 */
	public function refreshSigns(){
		foreach ($this->signTiles as $signTile){
			if($signTile->level != null){
				
				$signTile->setText(OneVsOne::SIGN_TITLE, "-Waiting " . $this->getNumberOfPlayersInQueue(), "-Arenas: " . $this->getNumberOfFreeArenas(), "-+===+-");
			}
		}
	}
}




