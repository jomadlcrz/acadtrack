<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Prerequisite extends Model
{
    protected $table = 'prerequisites';

    public $timestamps = false;

    protected $fillable = [
        'subject_id',
        'prerequisite_subject_id',
        'created_at',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function prerequisiteSubject()
    {
        return $this->belongsTo(Subject::class, 'prerequisite_subject_id');
    }
}
