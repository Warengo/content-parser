<?php declare(strict_types = 1);

namespace Warengo\ContentParser;

final class ContentParserOptions {

	/** @var bool */
	protected $hashTags = false;

	/** @var bool */
	protected $urls = false;

	/** @var bool */
	protected $escapeBefore = false;

	/** @var bool */
	protected $breakLines = false;

	/** @var bool */
	protected $emoticons = false;

	public function setEmoticons(bool $emoticons = true): void {
		$this->emoticons = $emoticons;
	}

	public function setBreakLines(bool $breakLines = true): void {
		$this->breakLines = $breakLines;
	}

	public function setEscapeBefore(bool $escapeBefore = true): void {
		$this->escapeBefore = $escapeBefore;
	}

	public function setUrls(bool $urls = true): void {
		$this->urls = $urls;
	}

	public function setHashTags(bool $hashTags = true): void {
		$this->hashTags = $hashTags;
	}

	public function isEmoticons(): bool {
		return $this->emoticons;
	}

	public function isHashTags(): bool {
		return $this->hashTags;
	}

	public function isUrls(): bool {
		return $this->urls;
	}

	public function isEscapeBefore(): bool {
		return $this->escapeBefore;
	}

	public function isBreakLines(): bool {
		return $this->breakLines;
	}

}
