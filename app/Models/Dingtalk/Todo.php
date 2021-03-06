<?php

namespace App\Models\Dingtalk;

use App\Models\Traits\ListScopes;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use ListScopes;

    protected $table = 'dingtalk_todos';
    protected $fillable = [
        'create_staff',
        'create_realname',
        'todo_staff',
        'todo_name',
        'todo_userid',
        'create_time',
        'title',
        'url',
        'form_item_list',
        'data',
        'errcode',
        'errmsg',
        'record_id',
        'request_id',
        'step_run_id',
        'is_finish',
    ];
    protected $casts = [
        'data' => 'array',
        'form_item_list' => 'array',
    ];

    public function setFormItemListAttribute($value)
    {
        $this->attributes['form_item_list'] = json_encode($value);
    }

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }
}
