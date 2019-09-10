<?php

namespace AleoStudio\SalesForceRest;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;


class SalesForceRest
{
    // Auth properties.
    private $clientId;
    private $clientSecret;
    private $username;
    private $password;
    private $securityToken;
    private $authUrl;
    private $callbackUrl;

    // Instance properties.
    private $accessToken;
    private $instanceUrl;
    private $identityUrl;
    private $tokenExpiry;


    public function __construct($clientId, $clientSecret, $username, $password, $securityToken, $authUrl, $callbackUrl)
    {
        $this->clientId      = $clientId;
        $this->clientSecret  = $clientSecret;
        $this->username      = $username;
        $this->password      = $password;
        $this->securityToken = $securityToken;
        $this->authUrl       = $authUrl;
        $this->callbackUrl   = $callbackUrl;
    }


    public function test()
    {
        return $this->getAccessToken();
    }




    public function getAccessToken()
    {
        $accessToken = null;

        if (empty($this->accessToken)) {
            $this->authentication();
            $accessToken = $this->accessToken;
        }

        return $accessToken;
    }




    /**
     * The authorization flow. The grant parameter must be 'password' for the authentication flow
     * or 'refresh_token' to refresh the expired access token.
     * If the flow runs without issues, it set the auth properties to be used in the whole class.
     *
     * @param string $grant - The grant type (password - refresh_token)
     * @throws \Exception   - Returns an exception if the auth flow fails.
     */
    public function authentication($grant = 'password')
    {
        $client = new Client(['base_uri' => $this->authUrl]);

        try {
            $response = $client->post($this->authUrl, [
                RequestOptions::FORM_PARAMS => [
                    'grant_type'    => $grant,
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'username'      => $this->username,
                    'password'      => $this->password . $this->securityToken
                ]
            ]);

            $data = json_decode($response->getBody());

            $this->accessToken = $data->access_token;
            $this->instanceUrl = $data->instance_url;
            $this->tokenExpiry = $data->issued_at;
            $this->identityUrl = $data->id;

        } catch (\Exception $e) {
            throw new \Exception('Unable to connect to SalesForce: ' . $e);
        }
    }
}