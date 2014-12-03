<?php

namespace Minifixio\statspvp;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
        
use Minifixio\mffreeze\manager\FreezeManager;
use Minifixio\mffreeze\manager\UnFreezeTask;

class StatsPVP extends PluginBase  implements Listener{
    
	/**
	 * @var \SQLite3
	 */
	private $property;
    
	/**
	 * @var StatsCommand $command
	 */
	private $command;
	
    public function onEnable(){
    	@mkdir($this->getDataFolder());
    	$this->statsDB = new \SQLite3($this->getDataFolder()."StatsPVP.sqlite3");
    	$this->statsDB->exec(stream_get_contents($this->getResource("sqlite3.sql")));
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
    	
    	$this->command = new StatsCommand($this);
    	$this->getServer()->getCommandMap()->register("stats", $this->command);
	}
    
    public function onDisable() {
    	$this->statsDB->close();
    }
    
    private function incrementKill(Player $player){
    	$pseudo = $player->getName();
    	$result = $this->statsDB->query("SELECT nbKill FROM Stat where pseudo = '$pseudo'")->fetchArray(SQLITE3_ASSOC);;
    	$nbKill = $result["nbKill"];
    	if(!is_int($nbKill)){
    		$this->statsDB->exec("INSERT INTO Stat (pseudo, nbKill, nbDeath, nbCoin) VALUES ('$pseudo', 1, 0, 1)");
    	}
    	else{
    		$this->statsDB->exec("UPDATE Stat SET nbKill = nbKill + 1, nbCoin = nbCoin + 1 where pseudo = '$pseudo'");
    	}
    }
    
    private function incrementDeath(Player $player){
    	$pseudo = $player->getName();
    	$result = $this->statsDB->query("SELECT nbDeath FROM Stat where pseudo = '$pseudo'")->fetchArray(SQLITE3_ASSOC);;
    	$nbDeath = $result["nbDeath"];
    	if(!is_int($nbDeath)){
    		$this->statsDB->exec("INSERT INTO Stat (pseudo, nbKill, nbDeath, nbCoin) VALUES ('$pseudo', 0, 1, 0)");
    	}
    	else{
    		$this->statsDB->exec("UPDATE Stat SET nbDeath = nbDeath + 1 where pseudo = '$pseudo'");
    	}    	 
    }    
       
    public function onPlayerDeath(PlayerDeathEvent $event){
   		$victim = $event->getEntity();
   		$cause = $victim->getLastDamageCause();
   		  		
   		if ($cause instanceof EntityDamageByEntityEvent) {
   			$killer = $cause->getDamager(); 		
   			$this->logOnConsole($killer->getName() . " a tue " . $victim->getName());
   		
	   		if($killer instanceof Player){
	   			$this->incrementKill($killer);
	    	}
	    	if($victim instanceof Player){
	   			$this->incrementDeath($victim);
	    	}
   		}
    }    
    
    
    public function getStats($pseudo){
    	return $this->statsDB->query("SELECT * FROM Stat where pseudo = '$pseudo'")->fetchArray(SQLITE3_ASSOC);;
    }
    
    private function logOnConsole($message){
    	$this->getServer()->broadcastMessage($message);
    }
}