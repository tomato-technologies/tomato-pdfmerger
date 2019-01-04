<?php

namespace Tomato\PDFMerger;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return PDFMerger::class;
    }
}