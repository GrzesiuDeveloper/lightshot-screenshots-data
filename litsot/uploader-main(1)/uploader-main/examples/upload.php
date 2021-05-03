<?php 

use Imgur\Client;
use Prntsc\Uploader;

require_once '../vendor/autoload.php';

$config = require 'config.php';

$client = new Client;
$client->setOption('client_id', $config['client_id']);
$client->setOption('client_secret', $config['client_secret']);

$prntsc = new Uploader($client);
$responseUpload = $prntsc->upload('images/arnold.jpg');
$responseCache = $prntsc->uploadFromCache($responseUpload['result']['cache']);

print_r($responseUpload);
print_r($responseCache);
