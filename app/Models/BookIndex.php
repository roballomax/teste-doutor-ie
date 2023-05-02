<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookIndex extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'index_id',
        'title',
        'pages',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function innerIndexes()
    {
        return $this->hasMany(BookIndex::class, 'index_id', 'id');
    }
}
