<?php

declare(strict_types=1);

namespace AdminApi\Controller\Domain\Request;

use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromBody;
use Common\Shared\Http\Rule\Required;
use Common\Shared\Http\Rule\ValueObject;
use Common\Shared\ValueObject\Email;

#[FromBody]
final class CreateRequest extends AbstractInput
{
    public function __construct(
        #[Required]
        #[ValueObject(Email::class)]
        private readonly mixed $email = null,
    ) {}

    public function email(): Email
    {
        return new Email((string) $this->email, field: 'email');
    }
}
