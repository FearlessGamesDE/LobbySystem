<?php

namespace LobbySystem\command;

use LobbySystem\queue\Queue;
use LobbySystem\queue\QueueManager;
use LobbySystem\utils\Output;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class StartCommand extends Command
{
	public function __construct()
	{
		parent::__construct("start", "Force-Start the game", "/start");
		$this->setPermission("lobbysystem.command.start");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$sender instanceof Player) {
			return;
		}

		if (!$this->testPermission($sender)){
			return;
		}

		$queue = QueueManager::getQueueOf($sender);

		if(!$queue instanceof Queue){
			Output::send($sender, "not-in-queue");
			return;
		}

		$queue->tick = min($queue->tick, 11);
		$queue->tick(); //the server starts on tick 10
	}
}