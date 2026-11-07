<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $guarded = [];

    public function clusters(): HasMany
    {
        return $this->hasMany(Cluster::class);
    }
}

