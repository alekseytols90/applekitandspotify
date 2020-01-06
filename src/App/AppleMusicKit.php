<?php

namespace App;

use GuzzleHttp\Client;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Ecdsa\Sha256; // you can use Lcobucci\JWT\Signer\Ecdsa\Sha256 if you're using ECDSA keys


class AppleMusicKit
{
    private $teamId;
    private $keyId;
    private $privateKey;

    public function __construct($teamId, $keyId, $privateKey)
    {
        $this->teamId = $teamId;
        $this->keyId = $keyId;
        $this->privateKey = $privateKey;
    }

    public function getToken($force_generate = false)
    {
        $file = dirname(dirname(dirname(__FILE__))) . '/.apple.token';
        if (!is_file($file)) file_put_contents($file, '[]');
        $token_info = json_decode(file_get_contents($file));


        // token expiry threshold
        $token_expiry_threshold = time() + 200;

        // return existing token
        if (!$force_generate && !empty($token_info->expiry) && (int)$token_info->expiry >= $token_expiry_threshold) {
            return $token_info->token;
        }

        // generate new token
        $signer = new Sha256();
        $privateKey = new Key($this->privateKey);
        $time = time();
        $expiry = $time + 1200;

        $token = (new Builder())->issuedBy($this->teamId) // Configures the issuer (iss claim)
        ->withHeader('alg', 'ES256')
            ->withHeader('kid', $this->keyId)
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->expiresAt($expiry) // Configures the expiration time of the token (exp claim)
            ->getToken($signer, $privateKey); // Retrieves the generated token

        $token_info = array(
            'token' => (string)$token,
            'expiry' => $expiry,
        );

        // write token to file
        file_put_contents($file, json_encode($token_info));

        return (string)$token;
    }

    public function search($term)
    {
        $url = 'https://api.music.apple.com/v1/catalog/us/search';

        $client = new Client();
        $headers = array(
            'Authorization' => "Bearer " . $this->getToken(),
        );
        $response = $client->request('GET', $url, [
            'query' => ['term' => $term],
            'headers' => $headers,
        ]);

        $response = json_decode($response->getBody()->getContents());

        return $response;
    }
}