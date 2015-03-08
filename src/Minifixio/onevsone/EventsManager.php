<?php

namespace Minifixio\onevsone;

use Minifixio\onevsone\utils\PluginUtils;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;


/**
 * Manages PocketMineEvents
 */
class EventsManager implements Listener{

	/** @var ArenaManager */
	private $arenaManager;
	
	public function __construct(ArenaManager $arenaManager){
		$this->arenaManager = $arenaManager;
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$this->arenaManager->removePlayerFromQueueOrArena($player);
		PluginUtils::logOnConsole("Il y a " . $this->arenaManager->getNumberOfPlayersInQueue() . " joueur dans la queue");
	}
	
	public function onPlayerDeath(PlayerDeathEvent $event){
		$deadPlayer = $event->getEntity();
		$arena = $this->arenaManager->getPlayerArena($deadPlayer);
		if($arena != NULL){
			$arena->onPlayerDeath($deadPlayer);
		}
	}
}



