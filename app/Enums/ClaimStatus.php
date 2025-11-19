<?php

namespace App\Enums;

enum ClaimStatus: string
{
	case PENDING = 'PENDING';
	case APPROVED = 'APPROVED';
	case REJECTED = 'REJECTED';
	case WITHDRAWN = 'WITHDRAWN';

	public static function default(): self
	{
		return self::PENDING;
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
		return in_array($this, [self::APPROVED, self::REJECTED, self::WITHDRAWN], true);
	}
}
