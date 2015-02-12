<?php

namespace Minifixio\onevsone;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
        
use pocketmine\event\player\PlayerJoinEvent;

class OneVsOne extends PluginBase  implements Listener{
	
    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function onDisable() {
 
    }

    private function logOnConsole($message){
    	$this->getServer()->broadcastMessage($message);
    	
    }

}