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

// External packages.
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use \Exception;


class SalesForceRest
{
    /**
     * @var $salesforce  - The SalesForce authentication instance.
     * @var $accessToken - The current access token.
     * @var $instanceUrl - The current instance url.
     * @var $client      - The GuzzleHttp instance.
     */
    private $salesforce;
    private $accessToken;
    private $instanceUrl;
    private $client;




    /**
     * SalesForceRest constructor.
     * It does the SalesForce authentication and set the returned token and the instance url.
     *
     * @param  string $appId       - The SalesForce CLIENT ID.
     * @param  string $appSecret   - The SalesForce CLIENT SECRET.
     * @param  string $user        - The SalesForce username used by login.
     * @param  string $pass        - The SalesForce password used by login.
     * @param  string $secToken    - The SalesForce security token set in the app settings.
     * @param  string $authUrl     - The SalesForce full auth url.
     * @param  string $callbackUrl - The SalesForce full callback url.
     * @throws Exception
     */
    public function __construct($appId, $appSecret, $user, $pass, $secToken, $authUrl, $callbackUrl)
    {
        $this->salesforce  = new SFAuth($appId, $appSecret, $user, $pass, $secToken, $authUrl, $callbackUrl);
        $this->accessToken = $this->salesforce->getAccessToken();
        $this->instanceUrl = $this->salesforce->getInstanceUrl();

        $this->client = new Client();
    }




    /**
     * xxx
     *
     * @param  $query
     * @return array|mixed|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function query($query)
    {
        $url = $this->instanceUrl.'/services/data/v39.0/query';

        $request = $this->client->request('GET', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken ],
            'query'   => [ 'q' => $query ]
        ]);

        return json_decode($request->getBody(), true);
    }




    /**
     * xxx
     *
     * @param  $object
     * @param  array $data
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create($object, array $data)
    {
        $url = $this->instanceUrl.'/services/data/v39.0/sobjects/'.$object.'/';

        $request = $this->client->request('POST', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken, 'Content-type'  => 'application/json' ],
            'json' => $data
        ]);

        if ($request->getStatusCode() != 201) {
            throw new Exception('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }

        $response = json_decode($request->getBody(), true);

        return $response['id'];
    }




    /**
     * xxx
     *
     * @param  $object
     * @param  $id
     * @param  array $data
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update($object, $id, array $data)
    {
        $url = $this->instanceUrl.'/services/data/v39.0/sobjects/'.$object.'/'.$id;

        $request = $this->client->request('PATCH', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken, 'Content-type'  => 'application/json' ],
            'json' => $data
        ]);

        if ($request->getStatusCode() != 204) {
            throw new Exception('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }

        return $request->getStatusCode();
    }




    /**
     * xxx
     *
     * @param  $object
     * @param  $field
     * @param  $id
     * @param  array $data
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upsert($object, $field, $id, array $data)
    {
        $url = $this->instanceUrl.'/services/data/v39.0/sobjects/'.$object.'/'.$field.'/'.$id;

        $request = $this->client->request('PATCH', $url, [
            'headers' => [ 'Authorization' => 'OAuth '.$this->accessToken, 'Content-type'  => 'application/json' ],
            'json' => $data
        ]);

        if ($request->getStatusCode() != 204 && $request->getStatusCode() != 201) {
            throw new Exception('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }
        return $request->getStatusCode();
    }




    /**
     * xxx
     *
     * @param  $object
     * @param  $id
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($object, $id)
    {
        $url = $this->instanceUrl.'/services/data/v39.0/sobjects/'.$object.'/'.$id;

        $request = $this->client->request('DELETE', $url, ['headers' => [ 'Authorization' => 'OAuth '.$this->accessToken ]]);

        if ($request->getStatusCode() != 204) {
            throw new Exception('Error! '.$url.' failed with status '.$request->getStatusCode().'. Reason: '.$request->getReasonPhrase());
        }

        return true;
    }
}