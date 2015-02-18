<?php

namespace Minifixio\onevsone\utils;

use pocketmine\Server;

/**
 * Utility methods for my plugin
 */
class PluginUtils{
	
	/**
	 * Log on the server console
	 */
	public static function logOnConsole($message){
		$logger = Server::getInstance()->getLogger();
		$logger->info("[1vs1] " . $message);
	}
	
	/**
	 * Send message with previous empty lines
	 */
	public static function sendMessageWithSpaces($player,$message,$nbSpace = 1){
		for ($i=0; $i<$nbSpace; $i++){
			$player->sendMessage(" ");
		}
		$player->sendMessage("[1vs1] " . $message);
	}
}



