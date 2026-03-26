<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $server_id
 * @property float $cpu_usage
 * @property int $memory_bytes
 * @property int $disk_bytes
 * @property \Carbon\Carbon $created_at
 */
class ServerStatsHistory extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'server_stats_history';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'server_id',
        'cpu_usage',
        'memory_bytes',
        'disk_bytes',
        'network_rx_bytes',
        'network_tx_bytes',
        'created_at',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'cpu_usage' => 'float',
        'memory_bytes' => 'integer',
        'disk_bytes' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the server that owns the stats history.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
