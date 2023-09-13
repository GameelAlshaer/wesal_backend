<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
class Message extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'chat_id',
        'sender_id',
        'reciever_id',
        'content',
        'img_url',
        'status',
        'isImg',
        'isDeleted',
        'replyMsg',
        'updated_at',
        'created_at'
    ];

    public function user(){
        return $this->belongsTo(User::Class);
    }
}
