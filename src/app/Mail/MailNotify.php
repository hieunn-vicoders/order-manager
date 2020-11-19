<?php

namespace VCComponent\Laravel\Order\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailNotify extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->get_template('Đơn hàng mới từ webpress.vn !','order::mail.mail',['order',$this->order]);
    }
    public function get_template($subject, $view, $with) {
        return $this->subject($subject)->view($view)->with($with);
    }
}
