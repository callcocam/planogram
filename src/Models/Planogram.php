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
use Illuminate\Database\Eloquent\Model;

class Planogram extends Model
{
    use HasFactory, HasUlids, SoftDeletes; 

    protected $guarded = ['id'];

    protected $casts = [
        'status' => PlanogramStatus::class,
    ];

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'planogram_store');
    }

    public function gondolas()
    {
        return $this->hasMany(Gondola::class);
    }
} 