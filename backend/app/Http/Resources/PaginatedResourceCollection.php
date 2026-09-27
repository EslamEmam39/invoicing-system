<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginatedResourceCollection extends ResourceCollection
{
    public $collects;
    public function __construct(LengthAwarePaginator $paginator, string $resourceClass)
    {
        $this->collects = $resourceClass;

        parent::__construct($paginator);
    }
}
