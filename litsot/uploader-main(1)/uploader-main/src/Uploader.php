<?php

namespace Prntsc;

use Imgur\Client;

class Uploader
{
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(?Client $client = null)
    {
        $this->client = $client;
    }

    /**
     * Upload image to prnt.sc by local file or url.
     * 
     * @param string $path Local path to file or url.
     * 
     * @return array
     */
    public function upload(string $path): array
    {
        $imgurResponse = $this->client->api('image')->upload([
            'image' => base64_encode(file_get_contents($path)),
            'type'  => 'base64',
        ]);

        $uploadParams = $this->buildUploadRequestParams($imgurResponse);
        $uploadResponse = $this->uploadToPrntSc($uploadParams);

        return $this->processResponses($uploadResponse, $imgurResponse, $uploadParams);
    }

    /**
     * Upload image to prnt.sc from cache.
     * 
     * @param string $cache Cached result from `upload` method.
     * 
     * @return array
     */
    public function uploadFromCache(string $cache): array
    {
        $response = $this->uploadToPrntSc($cache);
        return $this->processCacheResponse($response, $cache);
    }

    /**
     * Process upload prntsc response from cache. 
     * 
     * @param array $response
     * @param string $cache
     * @return array
     */
    private function processCacheResponse($response, $cache): array
    {
        $response = json_decode($response, true);

        if (@$response['result']['success'] == true) {
            return [
                'ok' => true,
                'result' => [
                    'prntsc' => $response['result']['url'],
                    'imgur' => null,
                    'cache' => $cache,
                ],
            ];
        }

        return [
            'ok' => false,
            'result' => [
                'prntsc' => $response,
                'imgur' => null,
            ],
        ];
    }

    /**
     * Process upload prntsc response. 
     * 
     * @param array $uploadResponse
     * @param array $imgurResponse
     * @param array $uploadParams
     * @return array
     */
    private function processResponses($uploadResponse, $imgurResponse, $uploadParams): array
    {
        $uploadResponse = json_decode($uploadResponse, true);

        if (@$uploadResponse['result']['success'] == true) {
            return [
                'ok' => true,
                'result' => [
                    'prntsc' => $uploadResponse['result']['url'],
                    'imgur' => $imgurResponse['link'],
                    'cache' => json_encode($uploadParams),
                ],
            ];
        }

        return [
            'ok' => false,
            'result' => [
                'prntsc' => $uploadResponse,
                'imgur' => $imgurResponse,
            ],
        ];
    }

    /**
     * Build request params for uplaod image.
     * 
     * @param array $response
     * @return array
     */
    private function buildUploadRequestParams($response): array
    {
        return [
            'jsonrpc' => "2.0",
            'method' => "save",
            'id' => "1",
            'params' => [
                'img_url' => $response['link'],
                'thumb_url' => $response['link'],
                'delete_hash' => $response['deletehash'],
                'app_id' => $this->generateRandomAppId(),
                'width' => $response['width'],
                'height' => $response['height'],
                'dpr' => '1',
            ],
        ];
    }

    /**
     * Generate random `app_id`.
     * 
     * @return string
     */
    private function generateRandomAppId(): string
    {
        $chars = array_merge(['F', 'C', 'D', 'E'], range(0, 9));
        return '{' . $this->randomStr($chars, 8) . '-' . $this->randomStr($chars, 4) . '-' . $this->randomStr($chars, 12) . '}';
    }

    /**
     * Generate random string.
     * 
     * @param array $chars
     * @param integer $lenght
     * @return string
     */
    private function randomStr(array $chars = [], int $lenght = 8): string
    {
        shuffle($chars);

        $string = '';
        for ($i = 0; $i < $lenght; $i++) {
            $string .= $chars[array_rand($chars)];
        }

        return $string;
    }

    /**
     * Upload image request to prnt.sc.
     *
     * @param array|string $params
     * @return void
     */
    private  function uploadToPrntSc($params)
    {
        $config = [
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/' . mt_rand(70, 87) . '.0.' . mt_rand(1200, 4280) . '.141 Safari/537.36',
            CURLOPT_URL => 'https://api.prntscr.com/v1/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POSTFIELDS => is_array($params) ? json_encode($params) : $params,
            CURLOPT_POST =>  true,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $config);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
