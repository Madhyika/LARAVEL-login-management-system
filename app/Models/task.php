<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'content',
        'completed',
        'user_id',
        'parent_id',
        'done',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function children()
    {
        return $this->hasMany(Task::class,'parent_id');
    } 
    public function parent()
    {
        return $this->belongsTo(Task::class,'parent_id');
    }
    public function scopeSearch($query, $search)
{
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%$search%")
              ->orWhere('content', 'like', "%$search%");
        });
    }
    return $query;
}


}
