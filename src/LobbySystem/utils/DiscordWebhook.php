<?php

namespace LobbySystem\utils;

use JsonException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;

class DiscordWebhook
{
	/**
	 * @param string  $webhook
	 * @param string  $message
	 * @param array[] $embeds
	 * @param string  $username
	 * @param bool    $async
	 */
	public static function send(string $webhook, string $message, array $embeds = [], string $username = "", bool $async = true): void
	{
		if ($async) {
			Server::getInstance()->getAsyncPool()->submitTask(new class($webhook, $message, $embeds, $username) extends AsyncTask {
				/**
				 * @var string
				 */
				private $webhook;
				/**
				 * @var string
				 */
				private $message;
				/**
				 * @var string
				 */
				private $embeds;
				/**
				 * @var string
				 */
				private $username;

				/**
				 * @param string     $webhook
				 * @param string     $message
				 * @param string[][] $embeds
				 * @param string     $username
				 */
				public function __construct(string $webhook, string $message, array $embeds, string $username)
				{
					$this->webhook = $webhook;
					$this->message = $message;
					$this->embeds = serialize($embeds);
					$this->username = $username;
				}

				public function onRun(): void
				{
					DiscordWebhook::send($this->webhook, $this->message, unserialize($this->embeds), $this->username, false);
				}
			});
			return;
		}

		$build = ["content" => $message];

		if ($embeds !== []) {
			$build["embeds"] = $embeds;
		}
		if ($username !== "") {
			$build["username"] = $username;
		}
		$build["avatar_url"] = Output::translate("avatarURL");

		try {
			$json = json_encode($build, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			return;
		}

		try {
			Internet::simpleCurl($webhook, 10, ["Content-Type: application/json"], [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $json,
				CURLOPT_HEADER => false
			]);
		} catch (InternetException $e) {
			return;
		}
	}

	/**
	 * @param string   $title
	 * @param string   $description
	 * @param string   $color
	 * @param string   $url
	 * @param string[] $author
	 * @param array[]  $fields
	 * @param string[] $image
	 * @param string[] $thumbnail
	 * @param string[] $footer
	 * @param int|null $timestamp
	 * @return array<int|string|array<string|array>>
	 */
	public static function buildEmbed(string $title = "", string $description = "", string $color = "", string $url = "", array $author = [], array $fields = [], array $image = [], array $thumbnail = [], array $footer = [], int $timestamp = null): array
	{
		$embed = [];

		if ($title !== "") {
			$embed["title"] = $title;
		}
		if ($description !== "") {
			$embed["description"] = $description;
		}
		if ($color !== "") {
			$embed["color"] = Color::toDecimal($color);
		}
		if ($url !== "") {
			$embed["url"] = $url;
		}
		if ($author !== []) {
			$embed["author"] = $author;
		}
		if ($fields !== []) {
			$embed["fields"] = $fields;
		}
		if ($image !== []) {
			$embed["image"] = $image;
		}
		if ($thumbnail !== []) {
			$embed["thumbnail"] = $thumbnail;
		}
		if ($footer !== []) {
			$embed["footer"] = $footer;
		}
		if ($timestamp !== null) {
			$embed["timestamp"] = date(DATE_ATOM, $timestamp);
		}

		return $embed;
	}

	/**
	 * @param string $name
	 * @param string $icon_url
	 * @param string $url
	 * @return string[]
	 */
	public static function buildAuthor(string $name, string $icon_url = "", string $url = ""): array
	{
		$author = ["name" => $name];

		if ($icon_url !== "") {
			$author["icon_url"] = $icon_url;
		}
		if ($url !== "") {
			$author["url"] = $url;
		}

		return $author;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param bool   $inline
	 * @return array<string|bool>
	 */
	public static function buildField(string $name, string $value, bool $inline = false): array
	{
		return ["name" => $name, "value" => $value, "inline" => $inline];
	}

	/**
	 * @param string $url
	 * @return string[]
	 */
	public static function buildImage(string $url): array
	{
		return ["url" => $url];
	}

	/**
	 * @param string $url
	 * @return string[]
	 */
	public static function buildThumbnail(string $url): array
	{
		return ["url" => $url];
	}

	/**
	 * @param string $text
	 * @param string $icon_url
	 * @return string[]
	 */
	public static function buildFooter(string $text, string $icon_url = ""): array
	{
		$footer = ["text" => $text];

		if ($icon_url !== "") {
			$footer["icon_url"] = $icon_url;
		}

		return $footer;
	}
}