<?php declare(strict_types = 1);

namespace Warengo\ContentParser;

use PHPHtmlParser\Dom;
use Warengo\ContentParser\Emoji\Emoji;
use Warengo\ContentParser\Nodes\TextNodeArray;
use Warengo\ContentParser\Nodes\TextNodeReplacer;

final class ContentParser {

	/** @var Dom */
	protected $dom;

	/** @var TextNodeReplacer[] */
	protected $replacers = [
		'url' => null,
		'hashTags' => null,
		'emoji' => null,
	];

	private function getUrlReplacer(): TextNodeReplacer {
		if (!$this->replacers['url']) {
			$replacer = new TextNodeReplacer();
			$replacer->setRegex('#(\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|(?:[^,[:punct:]\s]|/)))#i');
			$replacer->setCallback(function (string $value) {
				$tag = new Dom\Tag('a');
				$tag->setAttribute('href', $value);
				$tag->setAttribute('target', '_blank');
				$tag->setAttribute('rel', 'nofollow');

				$link = new Dom\HtmlNode($tag);
				$link->addChild(new Dom\TextNode($value));

				return $link;
			});
			$replacer->mustNotInsideTags(['a']);

			$this->replacers['url'] = $replacer;
		}

		return $this->replacers['url'];
	}

	private function getHashTagReplacer(): TextNodeReplacer {
		if (!$this->replacers['hashTags']) {
			$replacer = new TextNodeReplacer();
			$replacer->setRegex('@(?<=^|\s)(#\w{1,120})@u');
			$replacer->setCallback(function (string $value) {
				$tag = new Dom\Tag('a');
				$tag->setAttribute('href', $value);
				$tag->setAttribute('target', '_blank');

				$link = new Dom\HtmlNode($tag);
				$link->addChild(new Dom\TextNode($value));

				return $link;
			});
			$replacer->mustNotInsideTags(['a']);

			$this->replacers['hashTags'] = $replacer;
		}

		return $this->replacers['hashTags'];
	}

	private function getEmojiReplacer(): TextNodeReplacer {
		if (!$this->replacers['emoji']) {
			$replacer = new TextNodeReplacer();
			$replacer->setRegex('/:([-+\\w]+):/i');
			$replacer->setCallback(function (string $value) {
				$shortName = Emoji::shortnameToUrl(':' . $value . ':');
				if (!$shortName) {
					return null;
				}

				$tag = new Dom\Tag('img');
				$tag->setAttribute('alt', $value);
				$tag->setAttribute('src', Emoji::shortnameToUrl(':' . $value . ':'));
				$tag->setAttribute('class', 'emoji');

				$tag->selfClosing();
				$tag->noTrailingSlash();

				return new Dom\HtmlNode($tag);
			});

			$this->replacers['emoji'] = $replacer;
		}

		return $this->replacers['emoji'];
	}

	protected function replaceUrls(Dom $dom): void {
		$this->getUrlReplacer()->process(new TextNodeArray($dom->root));
	}

	protected function replaceHashTags(Dom $dom): void {
		$this->getHashTagReplacer()->process(new TextNodeArray($dom->root));
	}

	protected function replaceEmoticons(Dom $dom): void {
		$nodes = new TextNodeArray($dom->root);
		foreach ($nodes->toArray() as $node) {
			$node->setText(Emoji::toShortname($node->text()));

			$this->getEmojiReplacer()->processSingleTextNode($node);
		}
	}

	public function process(string $content, ContentParserOptions $options): string {
		$content = rtrim($content);

		if ($options->isEscapeBefore()) {
			$content = htmlspecialchars($content, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
		}

		if ($options->isBreakLines()) {
			$content = nl2br($content, false);
		}

		$dom = self::createDom($content);

		if ($options->isUrls()) {
			$this->replaceUrls($dom);
		}
		if ($options->isHashTags()) {
			$this->replaceHashTags($dom);
		}
		if ($options->isEmoticons()) {
			$this->replaceEmoticons($dom);
		}

		return (string) $dom;
	}

	public static function createDom(string $string): Dom {
		$dom = new Dom();
		$dom->addNoSlashTag([
			'area',
			'base',
			'basefont',
			'br',
			'col',
			'embed',
			'hr',
			'img',
			'input',
			'keygen',
			'link',
			'meta',
			'param',
			'source',
			'spacer',
			'track',
			'wbr'
		]);

		return $dom->loadStr($string);
	}

}
