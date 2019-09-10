<?php

namespace AleoStudio\SalesForceRest;

// External packages.
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use \Exception;


class SalesForceAuth implements SalesForceAuthInterface
{
    /**
     * Auth and instance properties.
     */
    private $appId;
    private $appSecret;
    private $user;
    private $pass;
    private $secToken;
    private $authUrl;
    private $callbackUrl;
    private $accessToken;
    private $instanceUrl;
    private $identityUrl;
    private $tokenExpiry;




    /**
     * SalesForceAuth constructor.
     *
     * @param string $appId       - The SalesForce CLIENT ID.
     * @param string $appSecret   - The SalesForce CLIENT SECRET.
     * @param string $user        - The SalesForce username used by login.
     * @param string $pass        - The SalesForce password used by login.
     * @param string $secToken    - The SalesForce security token set in the app settings.
     * @param string $authUrl     - The SalesForce full auth url.
     * @param string $callbackUrl - The SalesForce full callback url.
     */
    public function __construct($appId, $appSecret, $user, $pass, $secToken, $authUrl, $callbackUrl)
    {
        $this->appId       = $appId;
        $this->appSecret   = $appSecret;
        $this->user        = $user;
        $this->pass        = $pass;
        $this->secToken    = $secToken;
        $this->authUrl     = $authUrl;
        $this->callbackUrl = $callbackUrl;
    }




    /**
     * Retrieves the SalesForce access token. If it does not exist,
     * the authentication method is called.
     * TODO: handle the tokenExpiry to obtain a new token if the current one is expired.
     *
     * @return string $accessToken - Returns a valid access token.
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception      - Returns an exception if the auth flow fails.
     */
    public function authentication($grant = 'password', $format = 'json')
    {
        $params = [
            'grant_type'    => $grant,
            'client_id'     => $this->appId,
            'client_secret' => $this->appSecret,
            'username'      => $this->user,
            'password'      => $this->pass . $this->secToken,
            'format'        => $format
        ];

        // If the grant given is 'refresh' check if the actual access token is set. If the token is valid, add to the
        // param the refresh token, otherwise force the call to be a simple 'password' request to obtain a new token.
        if ($grant == 'refresh_token' && !empty($this->accessToken)) {
            $params['refresh_token'] = $this->accessToken;
        } else {
            $params['grant_type'] = 'password';
        }

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




    /**
     * Refresh the current access token.
     */
    public function refreshAccessToken()
    {
        // TODO: check the current token expiry
    }
}