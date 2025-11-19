<?php

namespace App\Enums;

enum FoundItemStatus: string
{
	case FOUND_UNCLAIMED = 'FOUND_UNCLAIMED';
	case CLAIM_PENDING = 'CLAIM_PENDING';
	case CLAIM_APPROVED = 'CLAIM_APPROVED';
	case COLLECTED = 'COLLECTED';

	public static function default(): self
	{
		return self::FOUND_UNCLAIMED;
	}

	public static function values(): array
	{
		return array_map(
			static fn (self $status) => $status->value,
			self::cases()
		);
	}

	public function isTerminal(): bool
	{
		return in_array($this, [self::CLAIM_APPROVED, self::COLLECTED], true);
	}
}



