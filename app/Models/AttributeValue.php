<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class AttributeValue extends Model
{
    use SoftDeletes;

    use BelongsToCompany;

    protected $fillable = ['attribute_id', 'company_id', 'value'];

    public function attribute() {
        return $this->belongsTo(Attribute::class);
    }
}

