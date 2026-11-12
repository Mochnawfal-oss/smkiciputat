<?php

namespace App\Models;

class Program extends ContentRecord
{
    protected $table = 'programs';

    public static function contentType(): string
    {
        return 'program';
    }
}
