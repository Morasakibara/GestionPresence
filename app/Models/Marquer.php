<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\FuncCall;

class Marquer extends Model
{
    use HasFactory;

    protected $table = 'marquer';

    protected $fillable = [
        'Empl_id',
        'id',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class,'Empl_id');
    }

    public function presence()
    {
        return $this->belongsTo(Presence::class,'id');
    }
}
