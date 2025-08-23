<?php

namespace GenialDigitalNusantara\LaragitVersion;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

class Facade extends IlluminateFacade
{
    protected static function getFacadeAccessor(): string
    {
        return 'gdn-dev.laragit-version';
    }
}
