<?php

namespace Contatoseguro\TesteBackend\Model;

class Product
{

    public $category;

    public function __construct(
        public int $id,
        public int $companyId,
        public string $title,
        public float $price,
        public bool $active,
        public string $createdAt
    ) {
    }

    public static function hydrateByFetch($fetch): self
    {
        return new self(
            (int)$fetch->id,
            (int)$fetch->company_id,
            (string)$fetch->title,
            (int)$fetch->price,
            (bool)$fetch->active,
            (string)$fetch->created_at
        );
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }
}
