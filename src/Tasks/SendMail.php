<?php

namespace The42Coders\Workflows\Tasks;

class SendMail extends Task
{
    public static $fields = [
        'Subject' => 'subject',
        'Recipients' => 'recipients',
        'Sender' => 'sender',
        'Content' => 'content',
        'Files' => 'files',
    ];

    public static $icon = '<i class="far fa-envelope"></i>';

    public function execute(): void
    {
        $dataBus = $this->dataBus;

        \Mail::html($dataBus->get('content'), function ($message) use ($dataBus) {
            $message->subject($dataBus->get('subject'))
                ->to($dataBus->get('recipients'))
                ->from($dataBus->get('sender'));
            $counter = 1;
            if (is_array($dataBus->get('files'))) {
                foreach ($dataBus->get('files') as $file) {
                    $message->attachData($file, 'Datei_'.$counter);
                    $counter++;
                }
            }
        });
    }
}
