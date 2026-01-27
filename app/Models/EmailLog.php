<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'app_env','mailer','mail_type','view',
        'recado_id','triggered_by_user_id',
        'from','reply_to','to','cc','bcc',
        'subject','body','body_hash','body_size',
        'status','sent_at','failed_at','attempt','duration_ms',
        'message_id','smtp_code','smtp_response',
        'error_message','error_trace',
        'ip','user_agent','trace_id',
    ];

    protected $casts = [
        'from' => 'array',
        'reply_to' => 'array',
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
