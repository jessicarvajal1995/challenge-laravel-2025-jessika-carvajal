<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_name',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const STATUS_INITIATED = 'initiated';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';

    const STATUSES = [
        self::STATUS_INITIATED,
        self::STATUS_SENT,
        self::STATUS_DELIVERED,
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function calculateTotal()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function canAdvanceStatus()
    {
        return in_array($this->status, [self::STATUS_INITIATED, self::STATUS_SENT]);
    }

    public function getNextStatus()
    {
        switch ($this->status) {
            case self::STATUS_INITIATED:
                return self::STATUS_SENT;
            case self::STATUS_SENT:
                return self::STATUS_DELIVERED;
            default:
                return null;
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_DELIVERED);
    }
} 