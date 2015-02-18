<?php

namespace Minifixio\onevsone\model;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\level\Position;

class Arena{

	public $active = FALSE;
	
	public $startTime;
	
	public $players = array();
	
	public $posX = -1;
	
	public $posY = -1;
	
	public $posZ = -1;
	
	/** @var Level */
	public $level = NULL;
	
	//Durée du round en seconde (= 3min )
	const ROUND_DURATION = 180;
	
	//Variable permettant d'arreter le timer du round
	private $taskHandler;

	/**
	 * Build a new Arena
	 * @param int $posX
	 * @param int $posY
	 * @param int $posZ
	 * @param string $level MCPE level for this arena 
	 */
	public function __construct($posX, $posY, $posZ, Level $level){
		$this->posX = $posX;
		$this->posY = $posY;
		$this->posZ = $posZ;
		$this->active = FALSE;
		$this->level = $level;
	}
	
	/** 
	 * Demarre un match.
	 * @param Player[] $players
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
		
		//Fixe l'heure de debut
		$this->$startTime = new Date();
		$this->$active = TRUE;
		
		//Lance la tache de cloture du round
		$task = new RoundCheckTask();
		$task->arena = $this;
		$this->taskHandler = Server::getInstance()->getScheduler()->scheduleDelayedTask($task, self::ROUND_DURATION);
		
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
		$player->getInventory()->setChesplate(Item::get(305, 0, 1));
		
		$player->sendMessage("[1vs1] Que le duel commence !");
		
   }
   
   /**
    * When a player was killed
    * @param Player $loser
    */
   public function onPlayerDeath(Player $loser){
   	
		//Finit le duel et teleporte le gagnant au spawn	
   		if($loser == $this->players[0]){
   			$winner = $this->players[1];
   		}
   		else{
   			$winner = $this->Player[0];
   		}
   		//On teleporte le gagnant au spawn
   		$winner->teleport($winner->getSpawn());
   		
   		//On reset l'arene
   		$this->reset();
   		
   		$winner->sendMessage("++++++++=++++++++");
   		$winner->sendMessage("Vous avez gangné le duel !");
   		$winner->sendMessage("++++++++=++++++++");
   		
   		
   		//On lui ajoute des points et des coins
   		//TODO:Lui donner des points de victoires
   }

   /**
    * Reset the Arena to current state
    */
   private function reset(){
   		//Rend une arene active apres un combat
   		$this->$active = FALSE;
   		$this->$players = array();
   		$this->$startTime = NULL;
   		Server::getInstance()->getScheduler()->cancelTask($this->taskHandler)->taskId();
   }
   
   /**
    * When a player quit the game
    * @param Player $loser
    */
   public function onPlayerQuit(Player $loser){
   		//Finit le duel quand un joueur quitte
   		//Tout est fait par la fonction onPlayerDeath ( voir plus haut )
   		$this->onPlayerDeath();
   }
   
   /**
    * When maximum round time is reached
    */
   public function onRoundEnd(){
   		foreach ($players as $player){
   			$player->teleport($player->getSpawn());
   			$player->sendMessage(" ");
   			$player->sendMessage("++++++++=++++++++");
   			$player->sendMessage("Temps de jeu dépassé. Duel arreté, pas de vainqueur !");
   			$player->sendMessage("++++++++=++++++++");
   			$player->sendMessage(" ");
   		}
	 }
}



