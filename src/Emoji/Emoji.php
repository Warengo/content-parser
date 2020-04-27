<?php declare(strict_types = 1);

namespace Warengo\ContentParser\Emoji;

use JoyPixels\Client;
use JoyPixels\RulesetInterface;

final class Emoji {

	/** @var Client|null */
	private static $client;

	private static function getClient(): Client {
		if (!self::$client) {
			self::$client = new Client();
			self::$client->imagePathPNG = 'https://cdn.jsdelivr.net/gh/joypixels/emoji-assets/png/32/';
		}

		return self::$client;
	}

	public static function getRuleset(): RulesetInterface {
		return self::getClient()->getRuleset();
	}

	public static function shortnameToUrl(string $shortname): ?string {
		$client = self::getClient();
		if (!isset($client->getRuleset()->getShortcodeReplace()[$shortname])) {
			return null;
		}

		$filename = $client->getRuleset()->getShortcodeReplace()[$shortname][1];

		return $client->imagePathPNG . $filename . $client->fileExtension;
	}

	public static function toShortname(string $text): string {
		$client = self::getClient();

		$text = $client->toShort($text);
		$text = preg_replace_callback(
			'/((\\s|^)' . $client->getRuleset()->getAsciiRegexp() . '(?=\\s|$|[!,.?]))/S',
			[self::class, 'asciiToShortname'],
			$text
		);

		return $text;
	}

	private static function asciiToShortname(array $matches): string {
		if (!isset($matches[3])) {
			return $matches[0];
		}

		$replace = self::getRuleset()->getAsciiReplace();
		$replaceWith = $matches[3];
		if (isset($replace[$matches[3]])) {
			$replaceWith = $replace[$matches[3]];
		}

		return $matches[2] . $replaceWith;
	}

}
