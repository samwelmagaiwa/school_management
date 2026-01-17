<?php

namespace App\Models\Accounting;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'invoice_id',
        'invoice_item_id',
        'fee_structure_id',
        'fee_installment_id',
        'amount',
        'amount_paid',
        'due_date',
        'status',
        'last_payment_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
        'last_payment_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function installmentDefinition()
    {
        return $this->belongsTo(FeeInstallment::class, 'fee_installment_id');
    }

}
