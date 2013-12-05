Native5 Services SDK for making calls onto the Native5 remote services api. 
============================================================================

[![Build Status](https://travis-ci.org/native5/native5-sdk-services-php.png)](https://  travis-ci.org/native5/native5-sdk-services-php)

Hmac Authentication is used to authenticate against the Hosted API server and invoke the remote services. 
Each application needs to provide its own key & secret which are to be provided as part of the 
application configuration. This key & secret is used to create a digest which is necessary for Hmac Authentication.

Available API's include :
* Identity
* Jobs
* Messaging
* Reports

Running
============================================================================

Pre-requisites :
 
 * Apache2 
 * PHP 5.2+ 
 * Pear (optional) 
