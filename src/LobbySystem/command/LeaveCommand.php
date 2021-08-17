<?php

namespace LobbySystem\command;

use LobbySystem\queue\Queue;
use LobbySystem\queue\QueueManager;
use LobbySystem\utils\Output;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class LeaveCommand extends Command
{
	public function __construct()
	{
		parent::__construct("leave", "Leave the queue", "/leave");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param string[]      $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void
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