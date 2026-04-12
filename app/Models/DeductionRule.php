<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeductionRule extends Model
{
    protected $fillable = ['name','type','value','is_active','applies_to'];
    protected function casts(): array { return ['is_active'=>'boolean','value'=>'decimal:2']; }
}
