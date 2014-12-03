<?php

namespace Minifixio\statspvp;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;

class StatsCommand extends Command implements PluginIdentifiableCommand{

	private $plugin;
	private $commandName = "stats";
	private $command;

	public function __construct(StatsPVP $plugin){
		parent::__construct($this->commandName, "Affiche les statistisques PVP");
		$this->setUsage("/$this->commandName");
		$this->plugin = $plugin;
		$this->command = $this->commandName;
	}

	public function getPlugin(){
		return $this->plugin;
	}

	public function execute(CommandSender $sender, $label, array $params){
		if(!$this->plugin->isEnabled()){
			return false;
		}

		if(!$sender instanceof Player){
			$sender->sendMessage("Utiliser la commande dans le jeu");
			return true;
		}
		
		$stats = $this->plugin->getStats($sender->getName());
		$sender->sendMessage("====== Vos stats =====");
		$sender->sendMessage("- Kills : " . $stats["nbKill"] );
		$sender->sendMessage("- Morts : " . $stats["nbDeath"] );
		$sender->sendMessage("- Coins : " . $stats["nbCoin"] );
		$sender->sendMessage("=====================");
		return true;
	}
}