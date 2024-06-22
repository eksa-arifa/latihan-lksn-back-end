<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post_attachment extends Model
{
    use HasFactory;


    protected $fillable = [
        "storage_path",
        "post_id"
    ];


    public function post(): BelongsTo{
        return $this->belongsTo(Post::class);
    }
}
