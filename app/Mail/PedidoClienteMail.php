<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoClienteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;

    // Le pasamos el pedido recién creado a la vista
    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu orden de reparación ha sido registrada, pronto se te contactará - Josetech',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pedido_cliente', // Ruta de la plantilla HTML
        );
    }
}
