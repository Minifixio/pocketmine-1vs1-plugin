<?php

namespace Minifixio\onevsone\model;

use pocketmine\scheduler\PluginTask;

class SignRefreshTask extends PluginTask{
	
	/** var ArenaManager **/
	public $arenaManager;
	
	public function onRun($currentTick){
		$this->arenaManager->refreshSigns();
	}
	
}