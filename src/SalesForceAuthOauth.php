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
use Stevenmaguire\OAuth2\Client\Provider\Salesforce as OAuth;


class SalesForceAuthOauth implements SalesForceAuthInterface
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




    /**
     * SalesForceAuth Oauth constructor.
     *
     * @param array $config - The full config array.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }




    /**
     * The authentication flow through OAuth2.
     */
    public function authentication(): void
    {
        $oauthParams = [
            'clientId'     => $this->config['clientId'],
            'clientSecret' => $this->config['clientSecret'],
            'redirectUri'  => $this->config['callbackUrl'],
        ];

        $auth = new OAuth($oauthParams);

        // TODO:
        // a) get access token, expiry date and refresh token from database if exists
        // b) check if token is still valid
        // c) if it is expired, request a refresh access token

        if (empty($this->accessToken)) {
            $authorizationUrl = $auth->getAuthorizationUrl();

            if (isset($_GET['code'])) {
                $tokenObject        = $auth->getAccessToken('authorization_code', ['code' => $_GET['code']]);
                $this->accessToken  = $tokenObject->getToken();
                $this->instanceUrl  = $tokenObject->getInstanceUrl();
                $this->refreshToken = $tokenObject->getRefreshToken();
                $this->tokenExpiry  = $tokenObject->getExpires();
            } else {
                header('Location: '.$authorizationUrl);
            }
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
