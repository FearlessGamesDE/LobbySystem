<?php

namespace LobbySystem\utils;

use JsonException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class DiscordWebhook
{
	/**
	 * @param string $webhook
	 * @param string $message
	 * @param array[] $embeds
	 * @param string $username
	 * @param bool $async
	 */
	public static function send(string $webhook, string $message, array $embeds = [], string $username = "", bool $async = true): void
	{
		if ($async) {
			Server::getInstance()->getAsyncPool()->submitTask(new class($webhook, $message, $embeds, $username) extends AsyncTask {
				private $webhook;
				private $message;
				private $embeds;
				private $username;

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

		$ch = curl_init($webhook);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

		curl_exec($ch);
	}

	/**
	 * @param string $title
	 * @param string $description
	 * @param string $color
	 * @param string $url
	 * @param array $author
	 * @param array[] $fields
	 * @param array $image
	 * @param array $thumbnail
	 * @param array $footer
	 * @param int|null $timestamp
	 * @return array
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
		if($footer !== []){
			$embed["footer"] = $footer;
		}
		if($timestamp !== null){
			$embed["timestamp"] = date(DATE_ATOM, $timestamp);
		}

		return $embed;
	}

	/**
	 * @param string $name
	 * @param string $icon_url
	 * @param string $url
	 * @return array
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
	 * @param bool $inline
	 * @return array
	 */
	public static function buildField(string $name, string $value, bool $inline = false): array
	{
		return ["name" => $name, "value" => $value, "inline" => $inline];
	}

	/**
	 * @param string $url
	 * @return array
	 */
	public static function buildImage(string $url): array
	{
		return ["url" => $url];
	}

	/**
	 * @param string $url
	 * @return array
	 */
	public static function buildThumbnail(string $url): array
	{
		return ["url" => $url];
	}

	/**
	 * @param string $text
	 * @param string $icon_url
	 * @return array
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