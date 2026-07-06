<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name'];

    public function values() {
        return $this->hasMany(AttributeValue::class);
    }
}

