OAuth2 Server Demo
==================

This is a small app running the [OAuth2 Server](https://github.com/bshaffer/oauth2-server-php) PHP library.

[Access the running demo!](http://brentertainment.com/oauth2/)

Installation
------------

**Dependencies**

[Composer](http://getcomposer.org/) is the fastest way to get this app up and running.  First, clone the repository.
Then, run composer to install the dependencies

    $ git clone git://github.com/bshaffer/oauth2-server-demo.git
    $ cd oauth2-server-demo
    $ curl -s http://getcomposer.org/installer | php
    $ composer.phar install

> composer.phar will be in your local directory.  You can also install this to your bin dir so you do not need to download it each time

**Host File**

Silex requires you to [configure your web server](http://silex.sensiolabs.org/doc/web_servers.html) to run it.

What does this app do??
-----------------------

This application simulates two applications talking to each other. The first app is the **Demo** app, which you will see
by accessing the homepage.  It looks like this:

![Demo Application Homepage](http://brentertainment.com/other/screenshots/demoapp-authorize.png)

Clicking *Authorize* will send you to the second applicaton, which mimics a data provider (such as twitter, facebook, etc).
This application, called *Lock'd In*, assumes you are already signed in, and asks if you'd like to grant the Demo app access
to your information:

![Lock'd In Authorization Request](http://brentertainment.com/other/screenshots/lockdin-authorize.png)

Once you click `Yes, I Authorize this Request`, you will be redirected back to the Demo application with an authorization
code, which [behind the scenes](https://github.com/bshaffer/oauth2-server-demo/blob/master/src/Demo/DemoControllerProvider.php)
is exchanged for an access token, which is then used to request your information from the Lock'd In API.

If all is successful, your data from Lock'd In will be displayed on the final page:

![Demo Application Granted](http://brentertainment.com/other/screenshots/demoapp-granted.png)

The OAuth2 Server
-----------------

The Lock'd In OAuth2 APIs implement the following endpoings:

   * `/api/authorize` - endpoint to receive an Authorization Code or an Implicit Token Grant
   * `/api/grant`     - endpoint to receive an Access Token by requesting one of the valid Grant Types
   * `/api/access`    - endpoint to access the protected resource (in this case, your friends) using an access token

These are the three main functions of an OAuth server, to authorize the user, grant the user tokens, and validate the token on
request to the APIs.
