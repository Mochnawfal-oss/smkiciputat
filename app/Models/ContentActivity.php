<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentActivity extends Model
{
    protected $fillable = [
        'admin_id',
        'content_type',
        'content_id',
        'action',
        'title',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
