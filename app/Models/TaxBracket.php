<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    protected $fillable = ['name','min_salary','max_salary','rate'];
    protected function casts(): array { return ['min_salary'=>'decimal:2','max_salary'=>'decimal:2','rate'=>'decimal:2']; }

    public static function calculateTax(float $grossSalary): float
    {
        $bracket = self::where('min_salary','<=',$grossSalary)
            ->where(function($q) use ($grossSalary) {
                $q->whereNull('max_salary')->orWhere('max_salary','>=',$grossSalary);
            })
            ->orderBy('min_salary','desc')
            ->first();
        if (!$bracket) return 0;
        return round($grossSalary * ((float)$bracket->rate / 100), 2);
    }
}
