<?php

namespace Minifixio\onevsone\model;

use pocketmine\Player;

class Arena{

	public $active = FALSE;
	
	public $startTime;
	
	public $players = array();
	
	public $posX = -1;
	
	public $posY = -1;
	
	public $posZ = -1;
	
	public $level = NULL;
	
	
	/** 
	 * Demarre un match.
	 * @param array $players
	 */
	public function startRound(array $players){
		$this->$players = $players;
		$player1 = $players[0];
		$plyer2 = $players[1];
		//Teleport le premier joueur
		$player1->teleport(new Position($this->$posX, $this->$posY, $this->$posZ, $this->level));
		
		//Teleport le deuxieme joueur
		$player2->teleport(new Position($pos->x + 0.5, $pos->y + 1, $pos->z + 0.5, $this->level));
		
		//Donne kit
		foreach ($players as $player){
			$this->giveKit($player);
		}
	}
	
	private function giveKit(Player $player){
		//Vide l'inventaire
		$player->getInventory->clearAll();
		
		//Donne une epee , armure et nourriture
		$player->getInventory()->addItem(Item::get(302, 0, 1));
		$player->getInventory()->addItem(Item::get(303, 0, 1));
		$player->getInventory()->addItem(Item::get(304, 0, 1));
		$player->getInventory()->addItem(Item::get(305, 0, 1));
		$player->getInventory()->addItem(Item::get(267, 0, 1));
		$player->getInventory()->addItem(Item::get(297, 0, 5));
		
		//Met l'armure sur lui
		$player->getInventory()->setHelmet(Item::get(302, 0, 1));
		$player->getInventory()->setBoots(Item::get(303, 0, 1));
		$player->getInventory()->setLeggins(Item::get(304, 0, 1));
		$player->getInventory()->setChesplate(Item::get(305, 0, 1))
		
	}
}


startRound(array<Player>(2)
onPlayerDeath()
onPlayerQuit()
onRoundEnd()
checkRoundEnd(): bool