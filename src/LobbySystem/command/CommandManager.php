<?php

namespace LobbySystem\command;

use LobbySystem\Loader;
use pocketmine\Server;

class CommandManager
{
	public static function load(): void
	{
		Server::getInstance()->getCommandMap()->registerAll("LobbySystem", [
			new PartyCommand(),
			new PlayCommand()
		]);
		if (Loader::isMaster()) {
			Server::getInstance()->getCommandMap()->registerAll("LobbySystem", [
				new LeaveCommand(),
				new StartCommand()
			]);
		}
	}
}