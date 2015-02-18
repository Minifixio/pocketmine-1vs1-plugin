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
		$this->arenaMager = $arenaManager;
	}
	
	public function onPlayerJoin(PlayerJoinEvent $event){
		PluginUtils::logOnConsole("a new player as join");
	}	

}



