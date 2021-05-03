<?php

use PHPUnit\Framework\TestCase;
use Prntsc\Uploader;
use Imgur\Client;

final class BotTest extends TestCase
{
    protected  function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }

    public function testCache(): void
    {
        $prntsc = new Uploader(new Client);
        
        $response = $prntsc->uploadFromCache('{"jsonrpc":"2.0","method":"save","id":"1","params":{"img_url":"https:\/\/i.imgur.com\/JWHrVdk.jpg","thumb_url":"https:\/\/i.imgur.com\/JWHrVdk.jpg","delete_hash":"Kxxgc00u0S6RMk0","app_id":"{6C3232F9-0402-5748814626DE}","width":500,"height":383,"dpr":"1"}}');

        $this->assertTrue($response['ok']);
    }
}
