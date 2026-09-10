<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceivedAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Nova encomenda] '.$this->order->ref.' — '.number_format((float) $this->order->total, 2, ',', ' ').'€',
            replyTo: [$this->order->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.orders.admin',
            with: ['order' => $this->order->loadMissing('items')],
        );
    }
}
