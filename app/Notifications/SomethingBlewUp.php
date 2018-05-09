<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class SomethingBlewUp extends Notification
{
    use Queueable;

    public $exception;

    protected $ignoredExceptions = [
        'Illuminate\Auth\AuthenticationException',
    ];

    public function __construct($exception)
    {
        $this->exception = $exception;
    }

    public function via($notifiable)
    {
        if ($this->shouldBeIgnored()) {
            return [];
        }
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->content("Exception! Argh! I'm Dying!")
            ->attachment(function ($attachment) {
                $attachment->title($this->exception->getMessage())
                    ->fields([
                        'line' => $this->exception->getLine(),
                        'file' => $this->exception->getFile(),
                        'summary' => $this->getExceptionTrace(),
                    ]);
            });
    }

    protected function shouldBeIgnored()
    {
        if (in_array(get_class($this->exception), $this->ignoredExceptions)) {
            return true;
        }
        if ($this->exception instanceof \Illuminate\Validation\ValidationException) {
            if (request()->is('login')) {
                return true;
            }
        }
        return false;
    }

    protected function getExceptionTrace()
    {
        $text = '';
        $entries = [];
        if (auth()->check()) {
            $entries[] = 'User ID : ' . auth()->user()->id;
            $entries[] = 'Username : ' . auth()->user()->username;
        }
        $entries[] = 'URL : ' . url()->full();
        $entries[] = 'UA : ' . \Request::server('HTTP_USER_AGENT');
        foreach ($this->exception->getTrace() as $entry) {
            if (array_key_exists('class', $entry)) {
                if (preg_match('/App/', $entry['class'])) {
                    $line = 'Unknown';
                    $function = 'Unknown';
                    if (array_key_exists('line', $entry)) {
                        $line = $entry['line'];
                    }
                    if (array_key_exists('function', $entry)) {
                        $function = $entry['function'];
                    }

                    $entries[] = $entry['class'] . ' / line "' . $line . '" / function "' . $function . '"';
                }
            }
        }
        $guff = substr($this->exception->__toString(), 0, 500);
        return implode("\n", $entries) . "\n" . $guff;
    }
}
