<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wage_type extends Model
{
    use HasFactory;
    // في app/Models/Wage.php
protected $fillable = ['segment_name', 'wage_amount', 'ratio'];

}
