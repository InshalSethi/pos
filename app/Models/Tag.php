<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use SoftDeletes;

    use HasFactory;
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name'];
}

