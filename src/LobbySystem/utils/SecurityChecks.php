<?php

namespace LobbySystem\utils;

use DirectoryIterator;
use LobbySystem\Loader;
use Phar;
use pocketmine\Server;

class SecurityChecks
{
	public static function checkCompressionStatus(): void
	{
		if (self::isCompressed(Server::getInstance()->getFilePath())) {
			Loader::getInstance()->getLogger()->warning("Detected compressed PocketMine-MP server phar");
		}

		/** @var DirectoryIterator $plugin */
		foreach (new DirectoryIterator(Server::getInstance()->getPluginPath()) as $plugin) {
			if (!$plugin->isDot() && $plugin->getExtension() === "phar" && self::isCompressed($plugin->getPathname())) {
				Loader::getInstance()->getLogger()->warning("Detected compressed plugin " . $plugin->getBasename());
			}
		}
	}

	private static function isCompressed(string $path): bool
	{
		$phar = new Phar($path);
		foreach ($phar as $file) {
			if ($file->isCompressed() !== false) {
				return true;
			}
		}
		return false;
	}
}