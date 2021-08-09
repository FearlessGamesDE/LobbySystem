<?php

namespace LobbySystem\utils;

use LobbySystem\Loader;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class Output
{
	/**
	 * @var mixed[]
	 */
	private static $messages;

	public static function load(): void
	{
		Loader::getInstance()->saveResource("messages.yml");
		$messages = new Config(Loader::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
		if ($messages->get("version") !== Loader::MESSAGES_VERSION) {
			unlink(Loader::getInstance()->getDataFolder() . "messages.yml");
			Loader::getInstance()->saveResource("messages.yml");
			Loader::getInstance()->getLogger()->info("Replaced messages");
		}
		$messages->reload();
		self::$messages = $messages->getAll();
	}

	/**
	 * @param Player|Player[]|string|string[] $player
	 * @param string $key
	 * @param array $args
	 * @param string $prefix
	 */
	public static function send($player, string $key, array $args = [], string $prefix = ""): void
	{
		if (is_array($player)) {
			foreach ($player as $p) {
				self::send($p, $key, $args, $prefix);
			}
		} elseif ($player instanceof Player) {
			$player->sendMessage(self::translate($prefix === "" ? "prefix" : $prefix) . self::replace($key, $args));
		} elseif (($p = Server::getInstance()->getPlayerExact($player)) instanceof Player) {
			self::send($p, $key, $args, $prefix);
		}
	}

	/**
	 * @param Player|Player[]|string|string[] $player
	 * @param string $key
	 * @param array $args
	 */
	public static function important($player, string $key, array $args = []): void
	{
		if (is_array($player)) {
			foreach ($player as $p) {
				self::important($p, $key, $args);
			}
		} elseif ($player instanceof Player) {
			$player->sendMessage(self::replace("important", ["{message}" => self::replace($key, $args)]));
		} elseif (($p = Server::getInstance()->getPlayerExact($player)) instanceof Player) {
			self::important($p, $key, $args);
		}
	}

	/**
	 * @param string $key
	 * @param array $args
	 * @return mixed|string|string[]
	 */
	public static function replace(string $key, array $args = [])
	{
		return str_replace(array_keys($args), array_values($args), self::translate($key));
	}

	/**
	 * @param string $key
	 * @return mixed|string
	 */
	public static function translate(string $key)
	{
		return self::$messages[$key] ?? self::$messages["message-" . $key] ?? $key;
	}
}