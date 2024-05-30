<?php

namespace Webkul\Customer\Repositories;

use Webkul\Core\Eloquent\Repository;

class RegisterDeviceRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    function model(): string
    {
        return 'Webkul\Customer\Contracts\RegisterDevice';
    }
}
