<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatchNote extends Model
{
    protected $fillable = ['date', 'content', 'status'];
}
