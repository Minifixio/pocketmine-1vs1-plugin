<?php

namespace Minifixio\onevsone\command;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;

use Minifixio\onevsone\OneVsOne;
use Minifixio\onevsone\ArenaManager;

class JoinCommand extends Command implements PluginIdentifiableCommand{

	private $plugin;
	private $arenaManager;
	private $commandName = "joinpvp";
	private $command;

	public function __construct(OneVsOne $plugin, ArenaManager $arenaManager){
		parent::__construct($this->commandName, "Rejoins une arene PVP");
		$this->setUsage("/$this->commandName");
		$this->command = $this->commandName;
		
		$this->plugin = $plugin;
		$this->arenaManager = $arenaManager;
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
		
		$this->arenaManager->addNewPlayerToQueue($sender);
		
		return true;
	}
}