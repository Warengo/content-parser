<?php declare(strict_types = 1);

namespace Warengo\ContentParser\Nodes;

use PHPHtmlParser\Dom;

class TextNodeReplacer {

	/** @var string */
	private $regex;

	/** @var callable */
	private $callback;

	/** @var array */
	private $notInsideTags = [];


	public function setRegex(string $regex) {
		$this->regex = $regex;
	}

	public function setCallback(callable $callback) {
		$this->callback = $callback;
	}

	public function mustNotInsideTags(array $tags) {
		$this->notInsideTags = $tags;
	}

	protected function getParentTags(Dom\InnerNode $node): array {
		$parent = $node;
		$tags = [];

		do {
			$tag = $parent->getTag()->name();
			$tags[$tag] = $tag;
		} while ($parent = $parent->getParent());

		return $tags;
	}

	public function processSingleTextNode(Dom\TextNode $node): void {
		$splits = preg_split($this->regex, $node->text(), -1, PREG_SPLIT_DELIM_CAPTURE);
		if (count($splits) === 1) {
			return;
		}

		$parent = $node->getParent();
		if ($this->notInsideTags) {
			$tags = $this->getParentTags($parent);
			foreach ($this->notInsideTags as $tag) {
				if (isset($tags[$tag])) {
					return;
				}
			}
		}

		$children = [];
		foreach ($splits as $index => $value) {
			if ($index % 2 === 1) {
				$el = ($this->callback)($value);
				if (!$el) {
					return;
				} else if (is_string($el)) {
					$el = new Dom\TextNode($value);
				}

				$children[] = $el;
			} else {
				$children[] = new Dom\TextNode($value);
			}
		}

		foreach ($children as $child) {
			$parent->addChild($child, $node->id());
		}

		$parent->removeChild($node->id());
	}

	public function process(TextNodeArray $array): void {
		foreach ($array->toArray() as $node) {
			$this->processSingleTextNode($node);
		}
	}

}
