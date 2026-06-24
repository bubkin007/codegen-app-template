<?php

declare(strict_types=1);

namespace Common\App\Service\Domain;

use Common\App\Models\DomainModel;
use Common\App\Repository\DomainRepository;
use Common\App\Service\AbstractService;
use Common\App\Service\Domain\Data\CreateDto;
use Common\Shared\Exception\ValidationException;
use Psr\Log\LoggerInterface;

final readonly class Service extends AbstractService
{
    public function __construct(
        LoggerInterface $logger,
        private DomainRepository $domainRepo,
    ) {
        parent::__construct($logger);
    }

    public function create(CreateDto $dto): DomainModel
    {
        if ($this->domainRepo->getOneByEmail($dto->email) !== null) {
            throw new ValidationException(messageKey: 'domain.email_already_exists', field: 'email');
        }

        $model = $this->domainRepo->getEmptyModel();
        $model->setEmail($dto->email);

        return $this->domainRepo->save($model);
    }
}
