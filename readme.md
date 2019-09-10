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
require_once __DIR__ . '/vendor/autoload.php';

use AleoStudio\SalesForceRest\SalesForceRest;
```
---
## Unit testing

Install PHPUnit and then run the test with the command:
```sh
phpunit --bootstrap vendor/autoload.php tests/SalesForceRestTest.php 
```
