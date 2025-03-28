<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Planogram\Models;

use Callcocam\Planogram\Enums\PlanogramStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\SoftDeletes;
use Callcocam\Raptor\Models\AbstractModel;


class Planogram extends AbstractModel
{
    use HasFactory, HasUlids, SoftDeletes; 

    protected $guarded = ['id'];

    protected $casts = [
        'status' => PlanogramStatus::class,
    ];
} 