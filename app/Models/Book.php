<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    public $fillable = [
        'user_publisher_id',
        'title',
    ];

    public function userPublisher()
    {
        return $this->belongsTo(User::class, 'user_publisher_id');
    }

    public function bookIndexes() 
    {
        return $this->hasMany(BookIndex::class)->whereNull('index_id');
    }
}
