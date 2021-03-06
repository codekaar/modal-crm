<?php

namespace Pho\Crm\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function instances()
    {
        return $this->hasMany(Instance::class, 'user_id', 'id');
    }

    public function serviceConversations()
    {
        return $this->hasManyThrough(ServiceConversation::class, ServiceTicket::class,
            'by', 'uuid', 'id', 'uuid');
    }

    public function serviceTickets()
    {
        return $this->hasMany(ServiceTicket::class, 'by', 'id');
    }

    public function accessTokens()
    {
        return $this->hasMany(AccessToken::class, 'user_id', 'id');
    }

    public function analytics()
    {
        return $this->hasManyThrough(Analytics::class, Instance::class,
            'user_id', 'id', 'id', 'uuid');
    }
}
