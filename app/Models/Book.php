<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'name',
        'my_class_id',
        'description',
        'author',
        'isbn',
        'category',
        'subject',
        'edition',
        'publisher',
        'publication_year',
        'language',
        'book_type',
        'url',
        'location',
        'is_reference_only',
        'cover_image_path',
        'total_copies',
        'issued_copies',
    ];

    public function copies()
    {
        return $this->hasMany(BookCopy::class);
    }

    public function loans()
    {
        return $this->hasManyThrough(BookLoan::class, BookCopy::class, 'book_id', 'book_copy_id');
    }

    public function getTotalCopiesCountAttribute(): int
    {
        return $this->copies()->count();
    }

    public function getAvailableCopiesCountAttribute(): int
    {
        return $this->copies()->where('status', 'available')->count();
    }

    public function getBorrowedCopiesCountAttribute(): int
    {
        return $this->copies()->where('status', 'borrowed')->count();
    }
}
