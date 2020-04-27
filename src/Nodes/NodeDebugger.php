<?php declare(strict_types = 1);

namespace Warengo\ContentParser\Nodes;

use PHPHtmlParser\Dom;

final class NodeDebugger {

	public static function stringTree(Dom\InnerNode $node, int $level = 0): void {
		$space = str_repeat('  ', $level);
		foreach ($node->getChildren() as $child) {
			if ($child->isTextNode()) {
				$substr = mb_substr($child->text(), 0, 50) . '...';
				echo $space . "#text($substr)\n";

				continue;
			}

			echo $space . "<{$child->getTag()->name()}>\n";

			if ($child instanceof Dom\InnerNode) {
				self::stringTree($child, $level + 1);
			}

			echo $space . "</{$child->getTag()->name()}>\n";
		}
	}

}
