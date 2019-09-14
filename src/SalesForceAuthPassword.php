<?php
/**
 * This file is part of the SalesForceRest package.
 *
 * (c) Alessandro Orrù <alessandro.orru@aleostudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AleoStudio\SalesForceRest;

// Package classes.
use AleoStudio\SalesForceRest\Exceptions\SalesForceException;

// External packages.
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;


class SalesForceAuthPassword implements SalesForceAuthInterface
{
    /**
     * @var $config       - The OAuth config.
     * @var $accessToken  - The current access token.
     * @var $refreshToken - The current refresh token.
     * @var $tokenExpiry  - The token expiry.
     * @var $instanceUrl  - The current instance url.
     */
    private $config;
    private $accessToken;
    private $refreshToken;
    private $tokenExpiry;
    private $instanceUrl;

    private $authUrl = 'https://login.salesforce.com/services/oauth2/token';




    /**
     * SalesForceAuth Password/security token constructor.
     *
     * @param array $config - The full config array.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }




    /**
     * The authentication flow. The grant parameter must be 'password' for the authentication
     * or 'refresh_token' to refresh the expired access token (password set as default).
     * If the flow runs without issues, it set the auth properties to be used in the whole class.
     *
     * @param  string $grant       - The grant type (password - refresh_token).
     * @param  string $format      - The return format: json - urlencoded - xml (default: json).
     * @throws SalesForceException - Returns an exception if the auth flow fails.
     */
    public function authentication(string $grant = 'password', string $format = 'json'): void
    {
        $params = [
            'grant_type'    => $grant,
            'client_id'     => $this->config['clientId'],
            'client_secret' => $this->config['clientSecret'],
            'username'      => $this->config['username'],
            'password'      => $this->config['password'] . $this->config['securityToken'],
            'format'        => $format
        ];

        // If the grant given is 'refresh' check if the actual access token is set. If the token is valid, add to the
        // param the refresh token, otherwise force the call to be a simple 'password' request to obtain a new token.
        if (($grant == 'refresh_token') && (!empty($this->accessToken))) {
            $params['refresh_token'] = $this->accessToken;
        } else {
            $params['grant_type'] = 'password';
        }

        try {
            // Post call through Guzzle.
            $client   = new Client(['base_uri' => $this->authUrl]);
            $response = $client->post($this->authUrl, [ RequestOptions::FORM_PARAMS => $params ]);

            switch ($format) {
                case 'json':
                    $data = json_decode($response->getBody());

                    // Calculates the custom hash to compare with the token signature to check if it is valid.
                    $hash = hash_hmac('sha256', $data->id . $data->issued_at, $this->appSecret, true);
                    if (base64_encode($hash) !== $data->signature) {
                        throw new SalesForceException('Token signature does not match. Access token is invalid.');
                    }

                    $this->accessToken = $data->access_token;
                    $this->instanceUrl = $data->instance_url;
                    $this->tokenExpiry = $data->issued_at;
                    $this->identityUrl = $data->id;
                    break;
                case 'xml':        break; // TODO: finish the XML auth handler.
                case 'urlencoded': break; // TODO: finish the URL encoded auth handler.
            }
        } catch (SalesForceException $e) {
            throw new SalesForceException('Unable to connect to SalesForce: '.$e);
        }
    }




    /**
     * Retrieves the SalesForce access token. If it does not exist,
     * the authentication method is called.
     *
     * @return string $accessToken - Returns a valid access token.
     * @throws SalesForceException
     */
    public function getAccessToken(): string
    {
        if (empty($this->accessToken)) {
            $this->authentication();
        }

        return $this->accessToken;
    }




    /**
     * Updates the current access token.
     *
     * @param string $accessToken - The given access token to update.
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }




    /**
     * Refresh the current access token.
     *
     * @return string $updatedToken - The new access token.
     */
    public function refreshAccessToken(): string
    {
        // TODO: check the current token expiry
        $updatedToken = null;

        return $updatedToken;
    }




    /**
     * Gets the current refresh token.
     *
     * @return string $refreshToken - The current access token.
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }




    /**
     * Sets the new refresh token.
     *
     * @param  string $refreshToken - The new refresh token to set.
     */
    public function setRefreshToken($refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }




    /**
     * Retrieves the SalesForce instance url after the authentication.
     *
     * @return string $instanceUrl - Returns the instance url.
     * @throws SalesForceException
     */
    public function getInstanceUrl(): string
    {
        if (empty($this->instanceUrl)) {
            $this->authentication();
        }

        return $this->instanceUrl;
    }
}
