<?php

namespace Minifixio\onevsone\model;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;


class GameTime extends PluginTask{
	
	const ROUND_DURATION = 180;
	
	private $arena;
	private $countdownValue;
	
	public function __construct(Plugin $owner, Arena $arena){
		parent::__construct($owner);
		$this->arena = $arena;
		$this->countdownValue = GameTime::ROUND_DURATION;
	}
	
	public function onRun($currentTick){
		if(count($this->arena->players) < 2){
			$this->arena->abortDuel();
		}
		else{
			$player1 = $this->arena->players[0];
			$player2 = $this->arena->players[1];
			
			if(!$player1->isOnline() || !$player2->isOnline()){
				$this->arena->abortDuel();
			}
			else{
				$player1->sendPopup(TextFormat::GOLD . TextFormat::BOLD . "Battle Ends in " . $this->countdownValue . TextFormat::RESET . " seconds");
				$player2->sendPopup(TextFormat::GOLD . TextFormat::BOLD . "Battle Ends in " . $this->countdownValue . TextFormat::RESET . " seconds");
				$this->countdownValue--;
				
				// If countdown is finished, start the duel and stop the task
				if($this->countdownValue == 0){
					$this->arena->onRoundEnd();
				}
			}
		}
	}
	
}
