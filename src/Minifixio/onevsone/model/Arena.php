<?php

namespace Minifixio\onevsone\model;

use Minifixio\onevsone\OneVsOne;
use Minifixio\onevsone\utils\PluginUtils;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Effect;
use pocketmine\entity\InstantEffect;

use \DateTime;

class Arena{

	public $active = FALSE;
	
	public $startTime;
	
	public $players = array();
	
	/** @var Position */
	public $position;
	
	// Roound duration (3min)
	const ROUND_DURATION = 180;
	
	const PLAYER_1_OFFSET_X = 5;
	const PLAYER_2_OFFSET_X = -5;
	
	// Variable for stop the round's timer
	private $taskHandler;
	private $countdownTaskHandler;

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
		
		// Set active to prevent new players
		$this->active = TRUE;
		
		// Set players
		$this->players = $players;
		$player1 = $players[0];
		$player2 = $players[1];
		
		$player1->sendMessage(TextFormat::BOLD . TextFormat::RED . ">> Duel against " . TextFormat::GOLD . $player2->getName());
		$player2->sendMessage(TextFormat::BOLD . TextFormat::RED . ">> Duel against " . TextFormat::GOLD . $player1->getName());

		// Create a new countdowntask
		$task = new CountDownToDuelTask(OneVsOne::getInstance(), $this);
		$this->countdownTaskHandler = Server::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 20);	
	}
	
	/**
	 * Really starts the duel after countdown
	 */
	public function startDuel(){
		
		Server::getInstance()->getScheduler()->cancelTask($this->countdownTaskHandler->getTaskId());
		
		$player1 = $this->players[0];
		$player2 = $this->players[1];
		
		$pos_player1 = Position::fromObject($this->position, $this->position->getLevel());
		$pos_player1->x += self::PLAYER_1_OFFSET_X;
		
		$pos_player2 = Position::fromObject($this->position, $this->position->getLevel());
		$pos_player2->x += self::PLAYER_2_OFFSET_X;
		$player1->teleport($pos_player1, 90, 0);
		$player2->teleport($pos_player2, -90, 0);
		
		// Give kit
		foreach ($this->players as $player){
			$this->giveKit($player);
		}
		
		// Fix start time
		$this->startTime = new DateTime('now');
		
		$player1->sendTip(TextFormat::RED . "You are strarting a duel !");
		$player1->sendMessage(" ");
		$player1->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
		$player1->sendMessage(TextFormat::AQUA . ">". TextFormat::RESET . " You're starting a duel against : " . $player2->getName() . " !");
		$player1->sendMessage(TextFormat::AQUA . ">". TextFormat::RESET . " You have 3min !");
		$player1->sendMessage(TextFormat::AQUA . ">". TextFormat::RESET . " Good luck :) !");
		$player1->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
		$player1->sendMessage(" ");
		
		$player1->sendTip(TextFormat::RED . "You are strarting a duel !");
		$player2->sendMessage(" ");
		$player2->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
		$player2->sendMessage(TextFormat::AQUA . ">". TextFormat::RESET ." You're starting a duel against : " . $player1->getName() . " !");
		$player2->sendMessage(TextFormat::AQUA . ">". TextFormat::RESET ." You have 3min !");
		$player2->sendMessage(TextFormat::AQUA . ">". TextFormat::RESET ." Good luck :) !");
		$player2->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
		$player2->sendMessage(" ");
		
		// Launch the end round task
		$task = new RoundCheckTask(OneVsOne::getInstance());
		$task->arena = $this;
		$this->taskHandler = Server::getInstance()->getScheduler()->scheduleDelayedTask($task, self::ROUND_DURATION * 20);
	}
	
	/**
	 * Abort duel during countdown if one of the players has quit
	 */
	public function abortDuel(){
		Server::getInstance()->getScheduler()->cancelTask($this->countdownTaskHandler->getTaskId());
	}
	
	private function giveKit(Player $player){
		// Clear inventory
		$player->getInventory()->clearAll();
		
		// Give sword, food and armor
		$player->getInventory()->addItem(Item::get(ITEM::IRON_SWORD));
		$player->getInventory()->addItem(Item::get(ITEM::BREAD));
		$player->getInventory()->setItemInHand(Item::get(ITEM::IRON_SWORD), $player);
		
		// Pur the armor on the player
		$player->getInventory()->setHelmet(Item::get(302, 0, 1));
		$player->getInventory()->setChestplate(Item::get(303, 0, 1));
		$player->getInventory()->setLeggings(Item::get(304, 0, 1));
		$player->getInventory()->setBoots(Item::get(305, 0, 1));
		$player->getInventory()->sendArmorContents($player);
		
		// Set his life to 20
		$player->setHealth(20);
		$player->removeAllEffects();

   }
   
   /**
    * When a player was killed
    * @param Player $loser
    */
   public function onPlayerDeath(Player $loser){
   	
		// Finish the duel and teleport the winner at spawn
   		if($loser == $this->players[0]){
   			$winner = $this->players[1];
   		}
   		else{
   			$winner = $this->players[0];
   		}  		
   		$loser->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
   		$loser->sendMessage(TextFormat::AQUA . "> You've lost the duel against " . $winner->getName() . " !");
   		$loser->sendMessage(TextFormat::AQUA . "> Try again next time !");
   		$loser->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
   		$loser->removeAllEffects();
   		
   		$winner->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
   		$winner->sendMessage(TextFormat::AQUA . ">> You've won the duel against " . $loser->getName() . " !");
   		$winner->sendMessage(TextFormat::AQUA . ">> You won with " . $winner->getHealth() . " of health !");
   		$winner->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
   		
   		// Teleport the winner at spawn
   		$winner->teleport($winner->getSpawn());

   		// Set his life to 20
   		$winner->setHealth(20);
   		Server::getInstance()->broadcastMessage(TextFormat::GREEN . TextFormat::BOLD . "» " . TextFormat::GOLD . $winner->getName() . TextFormat::WHITE . " won a duel against " . TextFormat::RED . $loser->getName() . TextFormat::WHITE . " !");
   		
   		// Reset arena
   		$this->reset();
   }

   /**
    * Reset the Arena to current state
    */
   private function reset(){
   		// Put active a rena after the duel
   		$this->active = FALSE;
   		foreach ($this->players as $player){
   			$player->getInventory()->setItemInHand(new Item(Item::AIR,0,0));
   			$player->getInventory()->clearAll();
   			$player->getInventory()->sendArmorContents($player);
   			$player->getInventory()->sendContents($player);
   			$player->getInventory()->sendHeldItem($player);
   		}
   		$this->players = array();
   		$this->startTime = NULL;
   		if($this->taskHandler != NULL){
   			Server::getInstance()->getScheduler()->cancelTask($this->taskHandler->getTaskId());
   		}
   }
   
   /**
    * When a player quit the game
    * @param Player $loser
    */
   public function onPlayerQuit(Player $loser){
   		// Finish the duel when a player quit
   		// With onPlayerDeath() function
   		$this->onPlayerDeath();
   }
   
   /**
    * When maximum round time is reached
    */
   public function onRoundEnd(){
   		foreach ($this->players as $player){
   			$player->teleport($player->getSpawn());
   			$player->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
   			$player->sendMessage(TextFormat::AQUA . "> Playing time over, no winners !");
   			$player->sendMessage(TextFormat::AQUA . "> Be faster next time !");  
   			$player->sendMessage(TextFormat::RED . TextFormat::BOLD . "++++++++=++++++++");
   			$player->removeAllEffects();
   		}
   		
   		// Reset arena
   		$this->reset();   		
	 }
	 
	 public function isPlayerInArena(Player $player){
	 	return in_array($player, $this->players);
	 }
}



