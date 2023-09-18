<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private $api_key;
    private $api_key_secret;

    public function __construct()
    {
        $this->api_key = getenv('API_KEY');
        $this->api_key_secret = getenv('API_KEY_SECRET');
    }
    
    public function send($to_email, $to_name, $subject, $content)
    {
        /*
        This call sends a message to the given recipient with vars and custom vars.
        */
        $mj = new Client($this->api_key,$this->api_key_secret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "",
                        'Name' => "My French Boutique"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 4722731,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();

    }
}
