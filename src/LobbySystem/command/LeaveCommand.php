<?php

namespace LobbySystem\command;

use LobbySystem\queue\Queue;
use LobbySystem\queue\QueueManager;
use LobbySystem\utils\Output;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class LeaveCommand extends Command
{
	public function __construct()
	{
		parent::__construct("leave", "Leave the queue", "/leave");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$sender instanceof Player) {
			return;
		}

		$queue = QueueManager::getQueueOf($sender);

		if (!$queue instanceof Queue) {
			Output::send($sender, "not-in-queue");
			return;
		}

		$queue->remove($sender);
	}
}