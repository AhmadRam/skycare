<?php

namespace Webkul\Core\Models;

use Webkul\Core\Eloquent\TranslatableModel;
use Webkul\Core\Contracts\CountryStateCity as CountryStateCityContract;

class CountryStateCity extends TranslatableModel implements CountryStateCityContract
{
    protected $fillable = ['default_name', 'country_code', 'state_code', 'country_id', 'country_state_id'];

    public $timestamps = false;

    public $translatedAttributes = ['default_name'];

    protected $with = ['translations'];

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        $array['default_name'] = $this->default_name;

        return $array;
    }


    /**
     * Get the State.
     */
    public function state()
    {
        return $this->belongsTo(CountryStateProxy::modelClass());
    }
}
