<?php

namespace Pho\Crm\Model;

use Illuminate\Database\Eloquent\Model;

class ServiceTicket extends Model
{
    protected $table = 'service-tickets';
    protected $primaryKey = 'uuid';

    public $incrementing = false;
    public $timestamps = false;

    const TYPE_SUPPORT = 1;
    const TYPE_RETENTION = 2;

    const STATUS_OPEN = 1;
    const STATUS_WAITING_RESPONSE = 2;
    const STATUS_CLOSED = 3;

    const FEEDBACK_UNHAPPY = 1;
    const FEEDBACK_NEUTRAL = 2;
    const FEEDBACK_HAPPY = 3;

    protected $fillable = [
        'uuid', 'title', 'type', 'by', 'open_date', 'status',
        'assignee', 'first_response_date', 'close_date', 'user_feedback',
    ];

    public function serviceConversations()
    {
        return $this->hasMany(ServiceConversation::class, 'uuid', 'uuid');
    }

    public function byUser()
    {
        return $this->belongsTo(User::class, 'by', 'id');
    }

    public function assigneeUser()
    {
        return $this->belongsTo(User::class, 'assignee', 'id');
    }
}
