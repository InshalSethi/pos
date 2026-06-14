<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = ['company_id', 'name'];

    public function values() {
        return $this->hasMany(AttributeValue::class);
    }
}
