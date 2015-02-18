<?php

namespace Minifixio\onevsone;

use Minifixio\onevsone\utils\PluginUtils;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;


/**
 * Manages PocketMineEvents
 */
class EventsManager implements Listener{

	/** @var ArenaManager */
	private $arenaManager;
	
	public function __construct(ArenaManager $arenaManager){
		$this->arenaManager = $arenaManager;
	}
	
	public function onPlayerJoin(PlayerJoinEvent $event){
		PluginUtils::logOnConsole("a new player as join");
	}	
	
	public function onPlayerDeath(PlayerDeathEvent $event){
		$deadPlayer = $event->getEntity();
		$arena = $this->arenaManager->getPlayerArena($deadPlayer);
		if($arena != NULL){
			$arena->onPlayerDeath($deadPlayer);
		}
		
	}
}



