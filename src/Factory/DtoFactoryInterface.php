<?php

namespace App\Factory;

use App\Dto\Input\InputDtoInterface;
use App\Dto\Output\OutputDtoInterface;
use App\Entity\EntityInterface;

interface DtoFactoryInterface
{
    public function fromEntity(EntityInterface $object): OutputDtoInterface;
    public function fromDto(InputDtoInterface $dto, ?EntityInterface $object = null): EntityInterface;
}