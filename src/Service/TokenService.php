<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class TokenService
{
    public function getDetail()
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://pro-api.coinmarketcap.com/v1/dex/token');
        $request->setRequestMethod('GET');
        $request->setBody($body);

        $request->setHeaders([
            'X-CMC_PRO_API_KEY' => 'YOUR_API_KEY'
        ]);

        $client->enqueue($request)->send();
        $response = $client->getResponse();

        echo $response->getBody();
    }
}
