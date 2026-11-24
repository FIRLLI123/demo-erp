<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClusterSoCutoff extends Model
{
    use HasFactory;

    protected $fillable = [
        'cluster_id',
        'close_time',
        'is_active',
        'is_force_closed',
        'force_closed_date',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_force_closed' => 'boolean',
        'force_closed_date' => 'date',
    ];

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    /**
     * Cek apakah SO saat ini buka (open) atau tutup (closed) untuk cluster ini.
     *
     * @param Carbon|null $now
     * @return array ['is_open' => bool, 'reason' => string, 'close_time' => string]
     */
    public function checkStatus(?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();

        // Jika aturan cutoff tidak diaktifkan, selalu buka
        if (!$this->is_active) {
            return [
                'is_open' => true,
                'status' => 'OPEN',
                'reason' => 'Cutoff tidak aktif',
                'close_time' => substr($this->close_time, 0, 5),
            ];
        }

        // Cek jika ada force close hari ini
        if ($this->is_force_closed && $this->force_closed_date) {
            $forceDate = $this->force_closed_date instanceof Carbon 
                ? $this->force_closed_date->format('Y-m-d') 
                : (string)$this->force_closed_date;

            // Hanya berlaku jika tanggalnya sama dengan hari ini.
            // Jika tanggal sudah lewat / beda hari, force close otomatis tidak berlaku!
            if ($forceDate === $now->format('Y-m-d')) {
                return [
                    'is_open' => false,
                    'status' => 'FORCE_CLOSED',
                    'reason' => $this->notes ?: 'Pemesanan hari ini telah ditutup oleh Administrator.',
                    'close_time' => substr($this->close_time, 0, 5),
                ];
            }
        }

        // Cek jam cutoff harian
        $currentTime = $now->format('H:i:s');
        $cutoffTime = strlen($this->close_time) === 5 ? $this->close_time . ':00' : $this->close_time;

        if ($currentTime >= $cutoffTime) {
            return [
                'is_open' => false,
                'status' => 'TIME_CLOSED',
                'reason' => 'Pemesanan telah ditutup melewati batas cut-off (' . substr($cutoffTime, 0, 5) . '). Buka kembali besok.',
                'close_time' => substr($cutoffTime, 0, 5),
            ];
        }

        return [
            'is_open' => true,
            'status' => 'OPEN',
            'reason' => 'Pemesanan dibuka sampai pukul ' . substr($cutoffTime, 0, 5),
            'close_time' => substr($cutoffTime, 0, 5),
        ];
    }
}
