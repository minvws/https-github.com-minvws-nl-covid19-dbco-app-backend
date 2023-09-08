<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Eloquent\EloquentMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use function is_callable;

class Message extends Mailable
{
    use SerializesModels;
    use Queueable;

    private EloquentMessage $eloquentMessage;

    /** @var callable|null $symfonyMessageCallable */
    private $symfonyMessageCallable;

    public function __construct(EloquentMessage $eloquentMessage, ?callable $symfonyMessageCallable = null)
    {
        $this->eloquentMessage = $eloquentMessage;
        $this->symfonyMessageCallable = $symfonyMessageCallable;
    }

    public function build(): self
    {
        $this->to($this->eloquentMessage->to_email, $this->eloquentMessage->to_name);
        $this->subject($this->eloquentMessage->subject);
        $this->from($this->eloquentMessage->from_email, $this->eloquentMessage->from_name);
        $this->view('mail.message', [
            'eloquentMessage' => $this->eloquentMessage, // message is a reserved keyword in a message-view
        ]);

        if (is_callable($this->symfonyMessageCallable)) {
            $this->withSymfonyMessage($this->symfonyMessageCallable);
        }

        return $this;
    }
}
