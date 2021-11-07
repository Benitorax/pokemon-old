<?php

namespace App\Entity\Traits;

use Symfony\Component\Uid\UuidV4;

trait UuidTrait
{
    /**
     * @ORM\Column(type="uuid", unique=true)
     */
    private UuidV4 $uuid;

    public function getUuid(): UuidV4
    {
        return $this->uuid;
    }
}