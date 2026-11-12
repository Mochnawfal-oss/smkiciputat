<?php

namespace App\Models;

class PrincipalWelcome extends ContentRecord
{
    protected $table = 'principal_welcomes';

    public static function contentType(): string
    {
        return 'principal_welcome';
    }
}
