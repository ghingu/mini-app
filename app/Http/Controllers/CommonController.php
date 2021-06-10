<?php


namespace App\Http\Controllers;


use App\User;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mail;

class CommonController extends AppBaseController
{

    public function sendMail($template, $subject, $email, $message_body = null)
    {
        $data = array('template' => $template, 'email' => $email, 'subject' => $subject, 'message_body' => $message_body);
        $result = Mail::send($data['template'], $data, function ($message) use ($data) {
            $message->to($data['email'])->subject($data['subject']);
        });
    }


}
