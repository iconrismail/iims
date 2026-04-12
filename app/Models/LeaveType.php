<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = ['name', 'days_per_year', 'is_paid'];
    protected function casts(): array { return ['is_paid' => 'boolean', 'days_per_year' => 'integer']; }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
}
