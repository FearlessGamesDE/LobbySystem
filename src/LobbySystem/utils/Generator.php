<?php

namespace LobbySystem\utils;


use Exception;
use LobbySystem\queue\QueueManager;
use pocketmine\utils\AssumptionFailedError;

class Generator
{
	/**
	 * @return int
	 */
	public static function generateQueueId(): int
	{
		try {
			$id = random_int(0, 999);
		}catch (Exception $exception){
			throw new AssumptionFailedError("999 is greater than 0...");
		}
		try {
			QueueManager::get($id);
		} catch (Exception $exception) {
			return $id;
		}

		return self::generateQueueId();
	}

	/**
	 * @param string $number
	 * @return string
	 */
	public static function toString(string $number): string
	{
		$chars = array_reverse(str_split($number));
		foreach ($chars as $i => $char) {
			$chars[$i] = (int) $char;
		}
		$string = "";
		$numbers = [
			0 => "",
			1 => "one",
			2 => "two",
			3 => "three",
			4 => "four",
			5 => "five",
			6 => "six",
			7 => "seven",
			8 => "eight",
			9 => "nine",
			10 => "ten",
			11 => "eleven",
			12 => "twelve",
			13 => "thirteen",
			14 => "fourteen",
			15 => "fifteen",
			16 => "sixteen",
			17 => "seventeen",
			18 => "eighteen",
			19 => "nineteen",
			20 => "twenty",
			30 => "thirty",
			40 => "forty",
			50 => "fifty",
			60 => "sixty",
			70 => "seventy",
			80 => "eighty",
			90 => "ninety",
			100 => "hundred",
			1000 => "thousand"
		];
		/** @var int[] $chars */
		foreach ($chars as $i => $char) {
			if ($char === 0) {
				continue;
			}
			switch ($i) {
				case 0:
					if (isset($chars[1]) && $chars[1] === 1) {
						continue 2;
					}
					$string = $numbers[$char];
					break;
				case 1:
					if ($char === 1) {
						$string = $numbers[10 + $chars[0]] . $string;
					} else {
						$string = $numbers[$char * 10] . $string;
					}
					break;
				case 2:
					$string = $numbers[$char] . $numbers[100] . $string;
					break;
				case 3:
					$string = $numbers[$char] . $numbers[1000] . $string;
			}
		}
		return $string;
	}
}