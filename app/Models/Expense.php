<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['spent_on', 'amount', 'description', 'receipt_path'];

    protected $hidden = ['receipt_path'];

    protected $appends = ['has_receipt'];

    protected function casts(): array
    {
        return [
            'spent_on' => 'date:Y-m-d',
            'amount' => 'decimal:2',
        ];
    }

    public function getHasReceiptAttribute(): bool
    {
        return $this->receipt_path !== null;
    }
}
