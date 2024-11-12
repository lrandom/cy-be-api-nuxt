<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //

    //convert status to string
    public function getStatusAttribute($value)
    {
        if ($value == 1) {
            return 'Pending';
        } elseif ($value == 2) {
            return 'Processing';
        } elseif ($value == 3) {
            return 'Completed';
        } elseif ($value == 4) {
            return 'Cancelled';
        }
    }
}
