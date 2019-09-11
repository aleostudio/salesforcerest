<?php
/***********************************************************************************************************************

EXAMPLE CODE TO TRY THE INTEGRATION
===================================
<?php
require_once __DIR__ . '/salesforcerest/vendor/autoload.php';

use AleoStudio\SalesForceRest\SalesForceRest;

// Config.
$appId       = 'xxxxxxxxxxxxxxx';
$appSecret   = 'yyyyyyyyyyyyyyy';
$user        = 'user@domain.com';
$pass        = 'password';
$secToken    = 'zzzzzzzzzzzzzzz';
$authUrl     = 'https://login.salesforce.com/services/oauth2/token';
$callbackUrl = 'https://login.salesforce.com/services/oauth2/success';

// Main instance.
$salesforce = new SalesForceRest($appId, $appSecret, $user, $pass, $secToken, $authUrl, $callbackUrl);

// Query example.
$response = $salesforce->query('SELECT Name, Id from Account LIMIT 100');

// Result handler.
foreach ($response['records'] as $row) {
    echo 'ID: '.$row['Id'].' - Name: '.$row['Name'].'<br/>';
}

***********************************************************************************************************************/

namespace AleoStudio\SalesForceRest;

// Package classes.
use AleoStudio\SalesForceRest\SalesForceAuth as SFAuth;
use AleoStudio\SalesForceRest\Exceptions\SalesForceException;

// External packages.
use GuzzleHttp\Client;


class SalesForceRest
{
    /**
     * @var $salesforce  - The SalesForce authentication instance.
     * @var $accessToken - The current access token.
     * @var $instanceUrl - The current instance url.
     * @var $apiVersion  - The current API version.
     * @var $client      - The GuzzleHttp instance.
     */
    private $salesforce;
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
     * @param  string $user        - The SalesForce username used by login.
     * @param  string $pass        - The SalesForce password used by login.
     * @param  string $secToken    - The SalesForce security token set in the app settings.
     * @param  string $authUrl     - The SalesForce full auth url.
     * @param  string $callbackUrl - The SalesForce full callback url.
     * @throws SalesForceException
     */
    public function __construct($appId, $appSecret, $user, $pass, $secToken, $authUrl, $callbackUrl)
    {
        $this->salesforce  = new SFAuth($appId, $appSecret, $user, $pass, $secToken, $authUrl, $callbackUrl);
        $this->accessToken = $this->salesforce->getAccessToken();
        $this->instanceUrl = $this->salesforce->getInstanceUrl();
        $this->client      = new Client();
    }




    /**
     * Method to query the SalesForce REST API through the SOQL syntax.
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/using_resources_working_with_searches_and_queries.htm
     *
     * It returns the result as an array/object.
     *
     * @param  $query
     * @return array|mixed|object
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function query($query)
    {
        $url = $this->instanceUrl.'/services/data/v'. $this->apiVersion .'/query';

        $request = $this->client->request('GET', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken ],
            'query'   => [ 'q' => $query ]
        ]);

        if ($request->getStatusCode() != 200)
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());

        return json_decode($request->getBody(), true);
    }


    /**
     * Creates a new SalesForce entity (object).
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_sobject_create.htm
     *
     * @param  $object
     * @param array $data
     * @return mixed
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create($object, array $data)
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/sobjects/'.$object.'/';

        $request = $this->client->request('POST', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken, 'Content-type'  => 'application/json' ],
            'json' => $data
        ]);

        if ($request->getStatusCode() != 201)
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());

        $response = json_decode($request->getBody(), true);

        return $response['id'];
    }




    /**
     * Updates a SalesForce entity (object) by its ID.
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_update_fields.htm
     *
     * @param  $object
     * @param  $id
     * @param  array $data
     * @return int
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update($object, $id, array $data)
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/sobjects/'.$object.'/'.$id;

        $request = $this->client->request('PATCH', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken, 'Content-type'  => 'application/json' ],
            'json' => $data
        ]);

        if ($request->getStatusCode() != 204)
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());

        return $request->getStatusCode();
    }




    /**
     * Updates (or creates) a SalesForce field of an entity (object) by its ID.
     * If not exists, it creates a new whole object.
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_upsert.htm
     *
     * @param  $object
     * @param  $field
     * @param  $id
     * @param  array $data
     * @return int
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upsert($object, $field, $id, array $data)
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/sobjects/'.$object.'/'.$field.'/'.$id;

        $request = $this->client->request('PATCH', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken, 'Content-type'  => 'application/json' ],
            'json' => $data
        ]);

        if ($request->getStatusCode() != 204 && $request->getStatusCode() != 201)
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());

        return $request->getStatusCode();
    }




    /**
     * Deletes a SalesForce entity (object) by its ID.
     * The full documentation can be found at:
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_delete_record.htm
     *
     * @param  $object
     * @param  $id
     * @return bool
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($object, $id)
    {
        $url = $this->instanceUrl.'/services/data/v'.$this->apiVersion.'/sobjects/'.$object.'/'.$id;

        $request = $this->client->request('DELETE', $url, ['headers' => [ 'Authorization' => 'OAuth '.$this->accessToken ]]);

        if ($request->getStatusCode() != 204)
            throw new SalesForceException('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());

        return true;
    }




    /**
     * Simple test method to try new features.
     */
    public function test()
    {
        return "test";
    }
}