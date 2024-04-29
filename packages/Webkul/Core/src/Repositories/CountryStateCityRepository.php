<?php

namespace Webkul\Core\Repositories;

use Webkul\Core\Eloquent\Repository;
use Prettus\Repository\Traits\CacheableRepository;
use Webkul\Core\Contracts\CountryStateCity;

class CountryStateCityRepository extends Repository
{
    use CacheableRepository;

    /**
     * Specify Model class name
     *
     * @return string
     */
    function model(): string
    {
        return CountryStateCity::class;
    }
}