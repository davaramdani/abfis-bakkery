<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_order';

    /**
     * The attributes that are mass assignable.
     * Ini adalah perbaikan utama untuk error Anda.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_user', 
        'total_price', 
        'status', 
        'customer_name',
        'phone',
        'address',
        'payment_method',
        'payment_status',
        'payment_proof',
        'order_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order_date' => 'datetime',
        'payment_status' => 'string',
        'payment_proof' => 'string',
    ];

    public function setPaymentStatusAttribute($value)
    {
        $validStatuses = ['pending', 'approved', 'rejected', 'completed'];
        if (in_array($value, $validStatuses)) {
            $this->attributes['payment_status'] = $value;
        } else {
            // Melempar exception jika status tidak valid adalah praktik yang baik.
            throw new \InvalidArgumentException("Invalid payment status: {$value}");
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Mendefinisikan relasi ke Product.
     * Perbaikan ada di baris ini, 'id_order' diubah menjadi 'order_id'.
     */
    public function products()
    {
        // 'order_id' adalah nama foreign key untuk Order di tabel pivot.
        // 'product_id' adalah nama foreign key untuk Product di tabel pivot.
        return $this->belongsToMany(Product::class, 'order_product', 'order_id', 'product_id')
                       ->withPivot('quantity', 'price_at_order');
    }

    public function updatePaymentStatus($status, $proof = null)
    {
        $data = ['payment_status' => $status];
        if ($proof) {
            $data['payment_proof'] = $proof;
        }

        return $this->update($data);
    }
}