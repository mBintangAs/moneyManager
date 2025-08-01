<?php

namespace App\Models;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded=[''];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
