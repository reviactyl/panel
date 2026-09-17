<?php

namespace App\Models;

/**
 * App\Models\Setting.
 *
 * @property string $key
 * @property string $value
 */
class Setting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'settings';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    public static array $validationRules = [
        'key' => 'required|string|between:1,191',
        'value' => 'string',
    ];
}
