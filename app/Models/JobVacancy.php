<?php

namespace App\Models;

class JobVacancy extends ContentRecord
{
    protected $table = 'job_vacancies';

    public static function contentType(): string
    {
        return 'job';
    }
}
