<?php declare(strict_types = 1);

namespace Warengo\ContentParser\Nodes;

use PHPHtmlParser\Dom;

class TextNodeArray {

	/** @var Dom\TextNode[] */
	protected $nodes = [];

	public function __construct(Dom\InnerNode $node) {
		$this->getTextNodes($node);
	}

	protected function getTextNodes(Dom\InnerNode $node) {
		foreach ($node->getChildren() as $child) {
			if ($child instanceof Dom\InnerNode) {
				$this->getTextNodes($child);
			} else if ($child instanceof Dom\TextNode) {
				$this->add($child);
			}
		}
	}

	protected function add(Dom\TextNode $textNode): void {
		$this->nodes[] = $textNode;
	}

	/**
	 * @return Dom\TextNode[]
	 */
	public function toArray(): array {
		return $this->nodes;
	}

}
