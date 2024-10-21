<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    use HasFactory;

    /**
     * Get the parent documentable model (user or team).
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
