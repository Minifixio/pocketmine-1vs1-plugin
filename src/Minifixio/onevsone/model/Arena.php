<?php

namespace Minifixio\onevsone\model;

use Minifixio\onevsone\OneVsOne;
use Minifixio\onevsone\utils\PluginUtils;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\item\Item;

use \DateTime;

class Arena{

	public $active = FALSE;
	
	public $startTime;
	
	public $players = array();

	/** @var Position */
	public $position;
	
	//Durée du round en seconde (= 3min )
	const ROUND_DURATION = 30;
	
	const PLAYER_1_OFFSET_X = 6;
	const PLAYER_2_OFFSET_X = -5;
	
	//Variable permettant d'arreter le timer du round
	private $taskHandler;

	/**
	 * Build a new Arena
	 * @param Position position Base position of the Arena
	 */
	public function __construct($position){
		$this->position = $position;
		$this->active = FALSE;
	}
	
	/** 
	 * Demarre un match.
	 * @param Player[] $players
	 */
	public function startRound(array $players){
		$this->players = $players;
		$player1 = $players[0];
		$player2 = $players[1];
		
		$pos_player1 = Position::fromObject($this->position, $this->position->getLevel());
		$pos_player1->x += self::PLAYER_1_OFFSET_X;
		
		$pos_player2 = Position::fromObject($this->position, $this->position->getLevel());
		$pos_player2->x += self::PLAYER_2_OFFSET_X;
		
		//Teleport le premier joueur
		$player1->teleport($pos_player1, 90, 0);
		
		//Teleport le deuxieme joueur
		$player2->teleport($pos_player2, -90, 0);
		
		//Donne kit
		foreach ($players as $player){
			$this->giveKit($player);
		}
		
		//Fixe l'heure de debut
		$this->startTime = new DateTime('now');
		$this->active = TRUE;
		
		$player1->sendMessage(" ");
		$player1->sendMessage("++++++++=++++++++");
		$player1->sendMessage(">> Vous commencez un duel contre : " . $player2->getName() . " !");
		$player1->sendMessage(">> Vous avez 3 min !");
		$player1->sendMessage(">> Bonne chance !");
		$player1->sendMessage("++++++++=++++++++");
		$player1->sendMessage(" ");
		
		$player2->sendMessage(" ");
		$player2->sendMessage("++++++++=++++++++");
		$player2->sendMessage(">> Vous commencez un duel contre : " . $player1->getName() . " !");
		$player2->sendMessage(">> Vous avez 3 min !");
		$player2->sendMessage(">> Bonne chance !");
		$player2->sendMessage("++++++++=++++++++");
		$player2->sendMessage(" ");
		
		
		
		//Lance la tache de cloture du round
		$task = new RoundCheckTask(OneVsOne::getInstance());
		$task->arena = $this;
		$this->taskHandler = Server::getInstance()->getScheduler()->scheduleDelayedTask($task, self::ROUND_DURATION * 20);
		
	}
	
	private function giveKit(Player $player){
		//Vide l'inventaire
		$player->getInventory()->clearAll();
		
		//Donne une epee , armure et nourriture
		$player->getInventory()->addItem(Item::get(267, 0, 1));
		$player->getInventory()->addItem(Item::get(297, 0, 5));
		
		//Met l'armure sur lui
		$player->getInventory()->setHelmet(Item::get(302, 0, 1));
		$player->getInventory()->setChestplate(Item::get(303, 0, 1));
		$player->getInventory()->setLeggings(Item::get(304, 0, 1));
		$player->getInventory()->setBoots(Item::get(305, 0, 1));
		$player->getInventory()->sendArmorContents($player);
		
		//On lui redonne toute sa vie
		$player->setHealth(20);

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
   			$winner = $this->players[0];
   		}  		
   		
   		$loser->sendMessage("++++++++=++++++++");
   		$loser->sendMessage(">> Vous avez perdu le duel contre" . $winner->getName() . " !");
   		$loser->sendMessage(">> Retentez votre chance la prochaine fois !");
   		$loser->sendMessage("++++++++=++++++++");
   		 
   		
   		$winner->sendMessage("++++++++=++++++++");
   		$winner->sendMessage(">> Vous avez gangné le duel contre : " . $loser->getName() . " !");
   		$winner->sendMessage(">> Vous avez gagné avec " . $winner->getHealth() . " de vie !");
   		$winner->sendMessage("+1 coins");
   		$winner->sendMessage("+1 kill");
   		$winner->sendMessage("++++++++=++++++++");

   		//On teleporte le gagnant au spawn
   		$winner->teleport($winner->getSpawn());

   		//On lui redonne toute sa vie
   		$winner->setHealth(20);
   		
   		Server::getInstance()->broadcastMessage("-> " . $winner->getName() . " a gagné un duel contre " . $loser->getName() . " !");
   		
   		
   		//On lui ajoute des points et des coins
   		//TODO:Lui donner des points de victoires

   		//On reset l'arene
   		$this->reset();
   }

   /**
    * Reset the Arena to current state
    */
   private function reset(){
   		//Rend une arene active apres un combat
   		$this->active = FALSE;
   		foreach ($this->players as $player){
   			$player->getInventory()->clearAll();
   			$player->getInventory()->setItemInHand(new Item(Item::AIR,0,0));
   			$player->getInventory()->sendArmorContents($player);
   			$player->getInventory()->sendContents($player);
   			$player->getInventory()->sendHeldItem($player);
   		}
   		$this->players = array();
   		$this->startTime = NULL;
   		Server::getInstance()->getScheduler()->cancelTask($this->taskHandler->getTaskId());
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
   		foreach ($this->players as $player){
   			$player->teleport($player->getSpawn());
   			$player->sendMessage(" ");
   			$player->sendMessage("++++++++=++++++++");
   			$player->sendMessage(">> Temps de jeu dépassé. Duel arreté, pas de vainqueur !");
   			$player->sendMessage(">> Soyez plus rapide la prochaine fois.");       
   			$player->sendMessage("++++++++=++++++++");
   			$player->sendMessage(" ");
   		}
   		
   		//On reset l'arene
   		$this->reset();   		
	 }
	 
	 public function isPlayerInArena(Player $player){
	 	return in_array($player, $this->players);
	 }
}



