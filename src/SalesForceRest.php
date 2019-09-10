<?php

namespace AleoStudio\SalesForceRest;

// Package classes.
use AleoStudio\SalesForceRest\SalesForceAuth as SFAuth;

// External packages.
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use \Exception;


class SalesForceRest
{
    /**
     * Auth and instance properties.
     */
    protected $clientId;
    protected $clientSecret;
    protected $username;
    protected $password;
    protected $securityToken;
    protected $authUrl;
    protected $callbackUrl;

    protected $accessToken;
    protected $instanceUrl;
    protected $identityUrl;
    protected $tokenExpiry;




    /**
     * SalesForceRest constructor.
     *
     * @param array $options - The option array that contains all the auth parameters.
     */
    public function __construct(array $options)
    {
        $this->clientId      = $options['clientId'];
        $this->clientSecret  = $options['clientSecret'];
        $this->username      = $options['username'];
        $this->password      = $options['password'];
        $this->securityToken = $options['securityToken'];
        $this->authUrl       = $options['authUrl'];
        $this->callbackUrl   = $options['callbackUrl'];
    }




    /**
     * Retrieves the SalesForce access token. If it does not exist,
     * the authentication method is called.
     * TODO: handle the tokenExpiry to obtain a new token if the current one is expired.
     *
     * @return string $accessToken - Returns a valid access token.
     * @throws
     */
    public function getAccessToken()
    {
        if (empty($this->accessToken)) $this->authentication();

        return $this->accessToken;
    }




    /**
     * Retrieves the SalesForce instance url after the authentication.
     *
     * @return string $instanceUrl - Returns the instance url.
     * @throws
     */
    public function getInstanceUrl()
    {
        if (empty($this->instanceUrl)) $this->authentication();

        return $this->instanceUrl;
    }




    /**
     * The authentication flow. The grant parameter must be 'password' for the authentication
     * or 'refresh_token' to refresh the expired access token (password set as default).
     * If the flow runs without issues, it set the auth properties to be used in the whole class.
     *
     * @param  string $grant  - The grant type (password - refresh_token).
     * @param  string $format - The return format: json - urlencoded - xml (default: json).
     * @throws \Exception     - Returns an exception if the auth flow fails.
     */
    public function authentication($grant = 'password', $format = 'json')
    {
        $params = [
            'grant_type'    => $grant,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username'      => $this->username,
            'password'      => $this->password . $this->securityToken,
            'format'        => $format
        ];

        // If the grant given is 'refresh' check if the actual access token is set.
        // If the access token is valid, add to the param the refresh token, otherwise
        // force the call to be a simple 'password' request to obtain a new token.
        if ($grant == 'refresh_token' && !empty($this->accessToken)) {
            $params['refresh_token'] = $this->accessToken;
        } else {
            $params['grant_type'] = 'password';
        }

        // POST call through Guzzle.
        try {
            $client   = new Client(['base_uri' => $this->authUrl]);
            $response = $client->post($this->authUrl, [ RequestOptions::FORM_PARAMS => $params ]);

            switch ($format) {
                case 'json':
                    $data = json_decode($response->getBody());
                    $this->accessToken = $data->access_token;
                    $this->instanceUrl = $data->instance_url;
                    $this->tokenExpiry = $data->issued_at;
                    $this->identityUrl = $data->id;
                    break;
                case 'xml':        break; // TODO: finish the XML auth handler.
                case 'urlencoded': break; // TODO: finish the URL encoded auth handler.
            }
        } catch (Exception $e) {
            throw new Exception('Unable to connect to SalesForce: '.$e);
        }
    }




    public function test()
    {
        $accessToken = $this->getAccessToken();

        $query = "SELECT Name, Id from Account LIMIT 100";
        $url = $this->instanceUrl."/services/data/v20.0/query?q=" . urlencode($query);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: OAuth ".$accessToken]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $total_size = $response['totalSize'];

        echo "$total_size record(s) returned<br/><br/>";
        foreach ((array) $response['records'] as $record) {
            echo $record['Id'] . ", " . $record['Name'] . "<br/>";
        }
        echo "<br/>";

        return $accessToken;
    }
}