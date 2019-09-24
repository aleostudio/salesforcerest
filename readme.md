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
If you already use composer in your project and you want use this package, require it with:
```sh
composer require aleostudio/salesforcerest
```
---
## SalesForce app creation

- Go to https://developer.salesforce.com/signup
- Register an user and then authenticate with it.
- From the dashboard, on the top right menu, click on **Switch to SalesForce Classic**
- Now, click on **Setup** (top right), enter **Apps** in the Quick Find box, select **Apps** (under Build | Create)
- Click now on **Connected app -> new** (bottom of the page).
- Enter the required fields and click on **Enable OAuth settings**
- Specify your **callback URL**. It must be the same as your application’s callback URL (only https:// works)
- Select the **OAuth scopes**: Perform requests on your behalf at any time (refresh_token, offline_access)
- When you click **Save**, the **Consumer Key** and **Consumer Secret** are created. Copy them in your config
- **Remember:** clientId = Consumer Key, clientSecret = Consumer Secret, and callbackUrl = Callback URL
---
## Code examples
- Create a new php file and try this code below (customizing the autoload path)
```sh
<?php
require_once __DIR__ . '/vendor/autoload.php';

use AleoStudio\SalesForceRest\SalesForceRest;

// OAuth credentials.
$config = [
    'clientId'     => 'YOUR_CLIENT_ID',
    'clientSecret' => 'YOUR_CLIENT_SECRET',
    'callbackUrl'  => 'https://your_domain/oauth_callback_url',
];

// Your stored data to avoid the authentication every time (empty object at the first time).
$storedToken = (object) [];
$storedToken = (object) [
    'accessToken'  => 'YOUR_STORED_ACCESS_TOKEN',
    'refreshToken' => 'YOUR_STORED_REFRESH_TOKEN',
    'instanceUrl'  => 'YOUR_STORED_INSTANCE_URL',
    'tokenExpiry'  => 'YOUR_STORED_TOKEN_EXPIRY_DATE'
];

// Main instance.
$sf = new SalesForceRest($config);


// If we already have a valid token object, we can bypass the auth flow.
if (!empty((array) $storedToken)) {
    // Sets the stored token into our SalesForce instance.
    $sf->setToken($storedToken);
} else {
    // OAuth authentication.
    // Set $authorize to true to force the auth or leave it to false if you want to use your stored token.
    $authorize = true;
    $sf->authentication($storedToken, $authorize);
}


// Query an entity using the SOQL syntax.
$response = $sf->query('SELECT Id, Name, Title, FirstName, LastName, Email from Contact LIMIT 10');
foreach ($response['records'] as $row) {
    echo 'ID: '.$row['Id'].' - Name: '.$row['Name'].' - Email: '.$row['Email'].'<br/>';
}


// Full entity fields list example.
$fields = $sf->getEntityFields('Contact');
foreach ($fields as $field) {
    echo 'Name: '.$field['name'].' - Label: '.$field['label'].' - Type: '.$field['type'].'<br />';
}


// CRUD methods list.
$results = $sf->query('SELECT Id, Name from Contact LIMIT 100');
$new_id  = $sf->create('Contact', ['FirstName'=>'John', 'LastName'=>'Doe', 'Email'=>'john.doe@domain.com']);
$update  = $sf->update('Contact', '0030b00002KgsnvAAB', ['FirstName'=>'Johnnnnn', 'LastName'=>'Doeeee', 'Title'=>null]);
$delete  = $sf->delete('Contact', '0030b00002KgsnvAAB');

// Helper methods list.
$lists  = $sf->getEntityLists('Contact');
$fields = $sf->getEntityFields('Contact');
$item   = $sf->getItem('Contact', '0030b00002KgsnvAAB');
$items  = $sf->getItems('Contact', 'LIST_ID');

// Auth methods list.
$authInstance = $sf->authentication((object)[], true);
$tokenObject  = $sf->getToken();
$accessToken  = $sf->getAccessToken();
$instanceUrl  = $sf->getInstanceUrl();
$sf->setToken($tokenObject);

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