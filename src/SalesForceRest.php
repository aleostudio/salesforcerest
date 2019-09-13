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
use Stevenmaguire\OAuth2\Client\Provider\Salesforce as SalesForceAuth;


class SalesForceRest
{
    /**
     * @var $auth        - The SalesForce authentication instance.
     * @var $accessToken - The current access token.
     * @var $instanceUrl - The current instance url.
     * @var $apiVersion  - The current API version.
     * @var $client      - The GuzzleHttp instance.
     */
    private $auth;
    private $accessToken;
    private $instanceUrl;
    private $client;

    /**
     * The current API version. We can retrieve all the available apis from:
     * https://[our_instance].salesforce.com/services/data/
     */
    private $apiVersion = '46.0';




    /**
     * SalesForceRest constructor.
     * It does the SalesForce authentication and set the returned token and the instance url.
     * A GuzzleClient instance will be created too.
     *
     * @param  string $appId       - The SalesForce CLIENT ID.
     * @param  string $appSecret   - The SalesForce CLIENT SECRET.
     * @param  string $callbackUrl - The SalesForce full callback url.
     */
    public function __construct(string $appId, string $appSecret, string $callbackUrl)
    {
        $oauthParams  = [ 'clientId' => $appId, 'clientSecret' => $appSecret, 'redirectUri' => $callbackUrl ];
        $this->auth   = new SalesForceAuth($oauthParams);
        $this->client = new Client();

        $this->authorization();
    }




    private function authorization()
    {
        // TODO:
        // a) get access token, expiry date and refresh token from database if exists
        // b) check if token is still valid
        // c) if it is expired, request a refresh access token

        if (empty($this->accessToken)) {
            $authorizationUrl = $this->auth->getAuthorizationUrl();

            if (isset($_GET['code'])) {
                $tokenObject       = $this->auth->getAccessToken('authorization_code', ['code' => $_GET['code']]);
                $this->accessToken = $tokenObject->getToken();
                $this->instanceUrl = $tokenObject->getInstanceUrl();
                $refreshToken      = $tokenObject->getRefreshToken();
                $expires           = $tokenObject->getExpires();
            } else {
                header('Location: '.$authorizationUrl);
            }
        }
    }




    /**
     * Method to query the SalesForce REST API through the SOQL syntax.
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/using_resources_working_with_searches_and_queries.htm
     *
     * The complete fields list for every entity can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.sfFieldRef.meta/sfFieldRef/salesforce_field_reference.htm
     *
     * It returns the result as an array.
     *
     * @param  string $query   - The SOQL format query to retrieve a resource.
     * @return array  $results - The query result (if valid).
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function query(string $query): array
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/query';

        $request = $this->client->request('GET', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken ],
            'query'   => [ 'q' => $query ]
        ]);

        if ($request->getStatusCode() != 200) {
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }

        return json_decode($request->getBody(), true);
    }




    /**
     * Creates a new SalesForce entity.
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_sobject_create.htm
     *
     * @param  string $entity - The entity type to create (Account, Contact, Customer, Attachment, Document...).
     * @param  array  $data   - The entity data.
     * @return string $id     - The created entity ID (if goes fine).
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(string $entity, array $data): string
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/sobjects/'.$entity.'/';

        $request = $this->client->request('POST', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken, 'Content-type'  => 'application/json' ],
            'json' => $data
        ]);

        if ($request->getStatusCode() != 201) {
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }

        $response = json_decode($request->getBody(), true);

        return $response['id'];
    }




    /**
     * Updates a SalesForce entity by its ID.
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_update_fields.htm
     *
     * @param  string $entity - The entity type to update (Account, Contact, Customer, Attachment, Document...).
     * @param  string $id     - The entity ID to update.
     * @param  array  $data   - The entity data.
     * @return int    $code   - The status code returned by the call.
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(string $entity, string $id, array $data): int
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/sobjects/'.$entity.'/'.$id;

        $request = $this->client->request('PATCH', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken, 'Content-type'  => 'application/json' ],
            'json' => $data
        ]);

        if ($request->getStatusCode() != 204) {
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }

        return $request->getStatusCode();
    }




    /**
     * Updates (or creates) a SalesForce field of an entity by its ID.
     * If not exists, it creates a new whole object.
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_upsert.htm
     *
     * @param  string $entity - The entity type to update (Account, Contact, Customer, Attachment, Document...).
     * @param  string $field  - The entity field to update.
     * @param  string $id     - The entity ID to update.
     * @param  array  $data   - The entity data.
     * @return int    $code   - The status code returned by the call.
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upsert(string $entity, string $field, string $id, array $data): int
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/sobjects/'.$entity.'/'.$field.'/'.$id;

        $request = $this->client->request('PATCH', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken, 'Content-type'  => 'application/json' ],
            'json' => $data
        ]);

        if ($request->getStatusCode() != 204 && $request->getStatusCode() != 201) {
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }

        return $request->getStatusCode();
    }




    /**
     * Deletes a SalesForce entity by its ID.
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_delete_record.htm
     *
     * @param  string $entity - The entity type to delete (Account, Contact, Customer, Attachment, Document...).
     * @param  string $id     - The entity ID to delete.
     * @return bool           - Returns true if deleted.
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete(string $entity, string $id): bool
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/sobjects/'.$entity.'/'.$id;

        $request = $this->client->request('DELETE', $url, ['headers' => [ 'Authorization' => 'OAuth '.$this->accessToken ]]);

        if ($request->getStatusCode() != 204) {
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }

        return true;
    }


    /**
     * Retrieves all the fields and the custom fields of a given entity (object).
     *
     * @param  string $entity  - The entity to analyze to get the full custom fields list
     * @return array  $results - The query result (if valid).
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEntityFields(string $entity): array
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/sobjects/'.$entity.'/describe/';
        $request = $this->client->request('GET', $url, ['headers'=>['Authorization'=>'OAuth '.$this->accessToken]]);

        if ($request->getStatusCode() != 200) {
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }

        return json_decode($request->getBody(), true)['fields'];
    }




    /**
     * Simple test method to try new features.
     */
    public function test()
    {
        return 'test';
    }
}