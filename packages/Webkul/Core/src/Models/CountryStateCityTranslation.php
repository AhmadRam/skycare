<?php

namespace Webkul\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Core\Contracts\CountryStateCityTranslation as CountryStateCityTranslationContract;

class CountryStateCityTranslation extends Model implements CountryStateCityTranslationContract
{
    public $timestamps = false;

    protected $fillable = ['default_name'];
}