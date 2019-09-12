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
composer require aleostudio/salesforcerest:dev-master
```
---
Then create a new php file and try this code below (customizing the autoload path if you have installed it by composer)
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

// Methods list.
$results = $salesforce->query('SELECT Id, Name from Contact LIMIT 100');
$new_id  = $salesforce->create('Contact', ['FirstName'=>'John', 'LastName'=>'Doe', 'Email'=>'john.doe@domain.com']);
$update  = $salesforce->update('Contact', '0030b00002KgsnvAAB', ['FirstName'=>'Johnnnnn', 'LastName'=>'Doeeee', 'Title'=>null]);
$delete  = $salesforce->delete('Contact', '0030b00002KgsnvAAB');
$fields  = $salesforce->getEntityFields('Contact');

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