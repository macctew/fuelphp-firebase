fuelphp-firebase
================

A Firebase client module for FuelPHP

Based on the work of [firebase-php](https://github.com/ktamas77/firebase-php) by @ktamas77

##[firebase-php](https://github.com/ktamas77/firebase-php)

Based on Firebase REST API: https://www.firebase.com/docs/rest-api.html

Base library: @ktamas77
Token auth: @craigrusso
Update & Push method: @mintao

##FuelPHP

* [Website](http://fuelphp.com/)
* [Release Documentation](http://docs.fuelphp.com)
* [Release API browser](http://api.fuelphp.com)
* [Development branch Documentation](http://dev-docs.fuelphp.com)
* [Development branch API browser](http://dev-api.fuelphp.com)
* [Support Forum](http://fuelphp.com/forums) for comments, discussion and community support

### Description

FuelPHP is a fast, lightweight PHP 5.3 framework. In an age where frameworks are a dime a dozen, We believe that FuelPHP will stand out in the crowd.  It will do this by combining all the things you love about the great frameworks out there, while getting rid of the bad.

##Installation

Copy to /app/modules/

Optionally enable in config.php: 'always_load' > 'modules'

Edit /firebase/config/firebase.php and add your firebaseio.com base url

Requires: php_curl

##Usage

Routable via: web://<project>/firebase/<action>/[<firebase_object>]

Omitting <action> default to GET

Actions are: [set, get, push, update, delete]

Via HMVC:

```php
$firebase = Request::forge('firebase/<action>/[<firebase_object>]/')->execute(
    array(
        array(
            'tree' => 'apple',
            'hello' => 'world
        ),
    ))->response();
```

