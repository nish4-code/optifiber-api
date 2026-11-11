<?php

namespace App\Models;

use App\Enums\NetworkStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiberSplice extends Model
{
    use HasFactory;

    protected $fillable = [
        'splice_closure_id',
        'tray_number',
        'strand_in_id',
        'strand_out_id',
        'nap_box_id',
        'loss_db',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tray_number' => 'integer',
            'loss_db' => 'float',
            'status' => NetworkStatus::class,
        ];
    }

    public function closure(): BelongsTo
    {
        return $this->belongsTo(SpliceClosure::class, 'splice_closure_id');
    }

    public function strandIn(): BelongsTo
    {
        return $this->belongsTo(FiberStrand::class, 'strand_in_id');
    }

    public function strandOut(): BelongsTo
    {
        return $this->belongsTo(FiberStrand::class, 'strand_out_id');
    }

    public function napBox(): BelongsTo
    {
        return $this->belongsTo(NapBox::class, 'nap_box_id');
    }
}
