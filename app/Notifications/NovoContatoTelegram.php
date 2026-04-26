<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class NovoContatoTelegram extends Notification
{
    protected $contato;

    /**
     * Create a new notification instance.
     */
    public function __construct($contato)
    {
        $this->contato = $contato;
    }

    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    public function toTelegram( $notifiable)
    {
        $url = route('contatos.index');

        return TelegramMessage::create()
            ->token(config('services.telegram-bot-api.token'))
            ->content("Novo contato recebido!\n\n
            . *Nome:* {$this->contato->nom_contato}\n
            . *Telefone:* {$this->contato->tel_contato}\n
            . *Email:* {$this->contato->eml_contato}\n
            . *Movimento:* {$this->contato->movimento->nom_movimento}\n
            . *Mensagem:* {$this->contato->txt_mensagem}")
            // ->button('Ver na Plataforma', route('contatos.index'));
            ->button('Ver na Plataforma', 'https://google.com');
    }
}
