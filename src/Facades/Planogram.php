<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Planogram\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Callcocam\Planogram\Planogram
 */
class Planogram extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Callcocam\Planogram\Planogram::class;
    }
}
