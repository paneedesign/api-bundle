<?php
/**
 * Created by PhpStorm.
 * User: fabianoroberto
 * Date: 11/09/15
 * Time: 09:22
 */

namespace PaneeDesign\ApiBundle\Oauth2;

use OAuth2;

class Client
{
    protected $client;
    protected $authEndpoint;
    protected $tokenEndpoint;
    protected $redirectUrl;
    protected $grant;
    protected $params;

    public function __construct(OAuth2\Client $client, $authEndpoint, $tokenEndpoint, $redirectUrl, $grant, $params)
    {
        $this->client        = $client;
        $this->authEndpoint  = $authEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->redirectUrl   = $redirectUrl;
        $this->grant         = $grant;
        $this->params        = $params;
    }

    public function getAuthenticationUrl() {
        return $this->client->getAuthenticationUrl($this->authEndpoint, $this->redirectUrl);
    }

    public function getAccessToken($code = null)
    {
        if ($code !== null) {
            $this->params['code'] = $code;
        }

        $response = $this->client->getAccessToken($this->tokenEndpoint, $this->grant, $this->params);

        if(isset($response['result']) && isset($response['result']['access_token'])) {
            $accessToken = $response['result']['access_token'];
            $this->client->setAccessToken($accessToken);
            return $accessToken;
        }

        throw new OAuth2\Exception(sprintf('Unable to obtain Access Token. Response from the Server: %s ', var_export($response)));
    }

    public function fetch($url)
    {
        return $this->client->fetch($url);
    }
}