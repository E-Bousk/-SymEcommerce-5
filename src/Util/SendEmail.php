<?php

namespace App\Util;

use Mailjet\Client;
use Mailjet\Resources;

class SendEmail
{
    private string $senderEmail;
    private string $senderName;
    private string $mailjetApiKeyPublic;
    private string $mailjetApiKeySecret;

    public function __construct(
        string $senderEmail,
        string $senderName,
        string $mailjetApiKeyPublic,
        string $mailjetApiKeySecret
    )
    {
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->mailjetApiKeyPublic = $mailjetApiKeyPublic;
        $this->mailjetApiKeySecret = $mailjetApiKeySecret;
    }

    public function send($emailTo, $emailName, $emailSubject, $emailContent)
    {

        $mj = new Client($this->mailjetApiKeyPublic, $this->mailjetApiKeySecret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From'             => [
                        'Email' => $this->senderEmail,
                        'Name'  => $this->senderName
                    ],
                    'To'               => [
                        [
                            'Email' => $emailTo,
                            'Name'  => $emailName
                        ]
                    ],
                    'TemplateID'       => 3778357,
                    'TemplateLanguage' => true,
                    'Subject'          => $emailSubject,
                    'Variables'        => [
                        'content' => $emailContent
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success() /** && dd($response->getData()) */;
    }
}
