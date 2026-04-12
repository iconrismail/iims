<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public    $incrementing = false;
    protected $keyType      = 'string';

    protected $fillable = ['key', 'value', 'group'];

    /** Get a setting value with optional default. */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /** Set a setting value. */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget("setting:{$key}");
    }

    /** Get all settings as a flat key→value array. */
    public static function allKeyed(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }

    /** Defaults used on first install. */
    public static function defaults(): array
    {
        return [
            // Company
            'company_name'    => ['value' => 'IIMS',            'group' => 'company'],
            'company_address' => ['value' => 'Freetown, Sierra Leone', 'group' => 'company'],
            'company_phone'   => ['value' => '',                'group' => 'company'],
            'company_email'   => ['value' => 'admin@iims.com',  'group' => 'company'],
            // Payroll
            'currency_symbol' => ['value' => 'NLE',             'group' => 'payroll'],
            'fiscal_year_start_month' => ['value' => '1',       'group' => 'payroll'],
            'include_saturdays' => ['value' => '0',             'group' => 'payroll'],
            // System
            'payslip_footer'  => ['value' => 'This is a computer-generated payslip.', 'group' => 'system'],
        ];
    }

    public static function seedDefaults(): void
    {
        foreach (static::defaults() as $key => $data) {
            static::firstOrCreate(['key' => $key], ['value' => $data['value'], 'group' => $data['group']]);
        }
    }
}
