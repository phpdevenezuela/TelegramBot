<?php

namespace App;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class BotTest extends TestCase
{
    public $client = null;

    public function setUp()
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:8000',
        ]);
    }

    public function test_newchatmember_success()
    {
        $data = [
            'message' => [
                'chat' => [
                    'id' => 133433434,
                ],
                'new_chat_member' => [
                    'id'         => 12345678,
                    'first_name' => 'AlexR1712',
                ],
            ],
        ];

        $body = json_encode($data);
        $r = $this->client->request('POST', 'webhook.php', ['body' => $body]);
        echo $r->getBody();
        file_put_contents('test', $r->getBody());
        $this->assertEquals(200, $r->getStatusCode());
    }
}
