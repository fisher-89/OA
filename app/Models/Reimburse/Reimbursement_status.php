<?php

namespace App\Models\Reimburse;

use Illuminate\Database\Eloquent\Model;

class Reimbursement_status extends Model
{
    //
    protected $connection = 'reimburse_mysql';
    public $table ='reimbursement_status';
}
