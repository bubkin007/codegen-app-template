<?php

declare(strict_types=1);

namespace Common\App\Service\Domain\Data;

use Common\Shared\ValueObject\Email;

final readonly class CreateDto
{
    public function __construct(
        public Email $email,
    ) {}
}
