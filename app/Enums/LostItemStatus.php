<?php

namespace App\Enums;

enum LostItemStatus: string
{
	case LOST_REPORTED = 'LOST_REPORTED';
	case RESOLVED = 'RESOLVED';

	public static function default(): self
	{
		return self::LOST_REPORTED;
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
		return $this === self::RESOLVED;
	}
}
