# Lightshot Uploader ([prnt.sc](https://prnt.sc/))

Easy upload screenshots to Lightshot (prnt.sc).

## Installation

```bash
$ composer require prntsc/uploader
```

## Usage

#### `upload(string $file): array`

Upload image to prnt.sc by local file or url.

```php

use Imgur\Client;
use Prntsc\Uploader;

require_once 'vendor/autoload.php';

$client = new Client;
$client->setOption('client_id', '48b6xxxxxxxxxxx');
$client->setOption('client_secret', 'fd5048fxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

$prntsc = new Uploader($client);

$response = $prntsc->upload('images/arnold.jpg');

print_r($response);

Array
(
    [ok] => 1
    [result] => Array
        (
            [prntsc] => http://prntscr.com/wnthmb
            [imgur] => https://i.imgur.com/FXjRMZ4.jpg
            [cache] => {"jsonrpc":"2.0","method":"save","id":"1","params":{"img_url":"https:\/\/i.imgur.com\/FXjRMZ4.jpg","thumb_url":"https:\/\/i.imgur.com\/FXjRMZ4.jpg","delete_hash":"FyUDDstquzhymYK","app_id":"{813F8739-7DC3-7EFFF9F0E13F}","width":500,"height":383,"dpr":"1"}}
        )

)
```

#### `uploadFromCache(string $cache): array`

Upload image to prnt.sc from cache.

```php

use Prntsc\Uploader;

require_once 'vendor/autoload.php';

$response = (new Uploader)->uploadFromCache('{"jsonrpc":"2.0","method":"save","id":"1","params":{"img_url":"https:\/\/i.imgur.com\/FXjRMZ4.jpg","thumb_url":"https:\/\/i.imgur.com\/FXjRMZ4.jpg","delete_hash":"FyUDDstquzhymYK","app_id":"{813F8739-7DC3-7EFFF9F0E13F}","width":500,"height":383,"dpr":"1"}}');

print_r($response);

// NOTE: imgur null, because result taken from cache.
Array
(
    [ok] => 1
    [result] => Array
        (
            [prntsc] => http://prntscr.com/wntjmx
            [imgur] =>
            [cache] => {"jsonrpc":"2.0","method":"save","id":"1","params":{"img_url":"https:\/\/i.imgur.com\/FXjRMZ4.jpg","thumb_url":"https:\/\/i.imgur.com\/FXjRMZ4.jpg","delete_hash":"FyUDDstquzhymYK","app_id":"{813F8739-7DC3-7EFFF9F0E13F}","width":500,"height":383,"dpr":"1"}}
        )

)
```

## PHP + Python = 🔥

Here a example of simple mass uploader script.

```bash
$ mkdir uploader && cd uploader
```

```bash
$ mkdir mkdir logs
```

> It is important, before that you have already uploaded several screenshots and your cache is saved in a file `logs/_cache.json` so that you do not upload a new picture every time.

**upload.php**

```php
<?php

use Imgur\Client;
use Prntsc\Uploader;

require_once 'vendor/autoload.php';

$client = new Client;
$client->setOption('client_id', 'YOUR_IMGUR_CLIENT_ID');
$client->setOption('client_secret', 'YOUR_IMGUR_CLIENT_SECRET');

$prntsc = new Uploader($client);

$cache = json_decode(file_get_contents('logs/_cache.json'), true);

$stats_file = __DIR__ . '/logs/stats_' . time() . mt_rand(0, 100) . mt_rand(0, 99999999) . '.json';

if (!file_exists($stats_file)) {
    file_put_contents($stats_file, json_encode(['success' => 0]));
}

$count = 0;
$fail = 0;

$chars = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
shuffle($chars);
$workerId = $chars[array_rand($chars)] . $chars[array_rand($chars)] . $chars[array_rand($chars)] . $chars[array_rand($chars)] . $chars[array_rand($chars)] . $chars[array_rand($chars)];

while (true) {
    shuffle($cache);

    $response = $prntsc->uploadFromCache($cache[array_rand($cache)]);

    if (!$response['ok']) {
        echo "error :(" . PHP_EOL;
        file_put_contents(__DIR__ . '/logs/error.log', date('d.m.Y H:i:s') . ' --> ' . json_encode($response) . "\n", FILE_APPEND);
        $fail++;
        sleep(60);
        continue;
    }

    $stats = json_decode(file_get_contents($stats_file));

    if (isset($stats->success)) {
        $stats->success++;
    }


    file_put_contents($stats_file, json_encode($stats));

    $count++;

    echo "[pid:{$workerId}] [{$count}|{$fail}] success: {$response['result']['prntsc']}" . PHP_EOL;
}
```

**run.py**

```python
import subprocess
import threading
import sys

def uploader():
    subprocess.run(["php", "upload.php"], stderr=sys.stderr, stdout=sys.stdout)

if len(sys.argv) == 1:
    count = 1
else:
    count = sys.argv[1]

print(f"Set count of threads: {count}")

threads = []

if __name__ == "__main__":
    for index in range(int(count)):
        th = threading.Thread(target=uploader)
        threads.append(th)
        th.start()

```

Time for magic ✨
```bash
$ python run.py <count_threads>
```
