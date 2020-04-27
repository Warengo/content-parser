<?php declare(strict_types = 1);

namespace Warengo\ContentParser\DI;

use Nette\DI\CompilerExtension;
use Warengo\ContentParser\ContentParser;

final class ContentParserExtension extends CompilerExtension {

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('contentParser'))
			->setFactory(ContentParser::class);
	}

}
