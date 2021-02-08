<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['created_at'];
    protected $table = 'medias';

    public function user () {
    	return $this->belongsTo(User::class);
    }

    public function getPathAttribute ($value) {
    	return url('/') . '/storage/uploads/' . $value;
    }

    public function getPathResizedAttribute ($value) {
    	return url('/') . '/storage/uploads/' . $value;
    }
}
