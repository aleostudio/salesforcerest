# SalesForce REST API package

A simple REST API integration to handle data from SalesForce CRM 

## Installation

Clone the package with the command:
```sh
git clone git@github.com:aleostudio/salesforcerest.git
```
Install its dependencies with:
```sh
composer install
```
If you use composer, type this command:
```sh
composer require aleostudio/salesforcerest
```
---
Then create a new php file and try this code below (customizing the autoload path)
```sh
<?php
require_once __DIR__ . '/vendor/autoload.php';

use AleoStudio\SalesForceRest\SalesForceRest;

$config = [
    // OAuth credentials.
    'clientId'     => 'YOUR_CLIENT_ID',
    'clientSecret' => 'YOUR_CLIENT_SECRET',
    'callbackUrl'  => 'https://your_domain/oauth_callback_url',
];

// Your stored data to avoid the authentication every time (empty string the first time).
$accessToken = 'YOUR_CURRENT_ACCESS_TOKEN_STORED_INTO_DB';
$instanceUrl = 'YOUR_CURRENT_INSTANCE_URL';

// Main instance.
$salesforce = new SalesForceRest($config, $accessToken, $instanceUrl);

// Prints the current access token and the instance url for test.
echo 'Access token: ' . $salesforce->getAccessToken() . '<br />';
echo 'Instance URL: ' . $salesforce->getInstanceUrl() . '<br />';

// Query an entity using the SOQL syntax.
$response = $salesforce->query('SELECT Id, Name, Title, FirstName, LastName, Email from Contact LIMIT 10');
foreach ($response['records'] as $row) {
    echo 'ID: '.$row['Id'].' - Name: '.$row['Name'].' - Email: '.$row['Email'].'<br/>';
}

// Full entity fields list example.
$fields = $salesforce->getEntityFields('Contact');
foreach ($fields as $field) {
    echo 'Name: '.$field['name'].' - Label: '.$field['label'].' - Type: '.$field['type'].'<br />';
}

// Full methods list.
$results     = $salesforce->query('SELECT Id, Name from Contact LIMIT 100');
$new_id      = $salesforce->create('Contact', ['FirstName'=>'John', 'LastName'=>'Doe', 'Email'=>'john.doe@domain.com']);
$update      = $salesforce->update('Contact', '0030b00002KgsnvAAB', ['FirstName'=>'Johnnnnn', 'LastName'=>'Doeeee', 'Title'=>null]);
$delete      = $salesforce->delete('Contact', '0030b00002KgsnvAAB');
$fields      = $salesforce->getEntityFields('Contact');
$accessToken = $salesforce->getAccessToken();
$instanceUrl = $salesforce->getInstanceUrl();

```
---
## Unit testing

Install PHPUnit for your OS:
```sh
# On MacOS through Homebrew:
brew install phpunit

# On Linux Ubuntu/Debian:
apt install phpunit

# By sources:
wget https://phar.phpunit.de/phpunit-8.3.4.phar
chmod +x phpunit-8.3.4.phar
sudo mv phpunit-8.3.4.phar /usr/local/bin/phpunit  
```
Run the test with the command:
```sh
phpunit --bootstrap vendor/autoload.php tests/SalesForceRestTest.php 
```
Or by Composer:
```sh
composer test tests/SalesForceRestTest.php
```