# SalesForce REST API package

A simple REST API integration to handle data from SalesForce CRM 

## Installation

Clone the package with the command:
```sh
git clone git@github.com:aleostudio/salesforce-rest.git
```
Install its dependencies with:
```sh
composer install
```
---
Then create a new php file and try this code below
```sh
<?php
require_once __DIR__ . '/salesforcerest/vendor/autoload.php';

use AleoStudio\SalesForceRest\SalesForceRest;

// Config.
$appId       = 'YOUR_CLIENT_ID';
$appSecret   = 'YOUR_CLIENT_SECRET';
$secToken    = 'YOUR_SECURITY_TOKEN';
$user        = 'youraccount@domain.com';
$pass        = 'yourpassword';
$authUrl     = 'https://login.salesforce.com/services/oauth2/token';
$callbackUrl = 'https://login.salesforce.com/services/oauth2/success';

// Main instance.
$salesforce = new SalesForceRest($appId, $appSecret, $user, $pass, $secToken, $authUrl, $callbackUrl);

// Query example.
$response = $salesforce->query('SELECT Id, Name, Email from Contact LIMIT 100');

// Result handler example.
foreach ($response['records'] as $row) {
    echo 'ID: '.$row['Id'].' - Name: '.$row['Name'].' - Email: '.$row['Email'].'<br/>';
}

```
---
## Unit testing

Install PHPUnit and then run the test with the command:
```sh
phpunit --bootstrap vendor/autoload.php tests/SalesForceRestTest.php 
```
