<?php

declare(strict_types=1);

namespace Common\App\Repository;

use Common\App\Models\DomainModel;
use Common\Shared\ValueObject\Uuid;
use Common\Shared\Util\Uuid as UuidUtil;
use Common\Shared\ValueObject\Email;
use DateTimeImmutable;

final readonly class DomainRepository
{
    public function __construct(
        private DomainModel $model,
        private UuidUtil $uuid,
    ) {}

    public function getEmptyModel(): DomainModel
    {
        return new DomainModel();
    }

    public function save(DomainModel $model): DomainModel
    {
        if ($model->isNew()) {
            $model->setId($this->uuid->generate());
            $model->setCreatedAt(new DateTimeImmutable());
        }

        $model->setUpdatedAt(new DateTimeImmutable());
        $model->save();
        $model->refresh();

        return $model;
    }

    public function getOneByEmail(Email $email): ?DomainModel
    {
        return $this->model->query()
            ->where([
                'email' => $email->value(),
            ])
            ->limit(1)
            ->one();
    }

    public function getOneById(Uuid $id): ?DomainModel
    {
        return $this->model->query()
            ->where([
                'id' => $id->value(),
            ])
            ->limit(1)
            ->one();
    }
}
