OAuth2 Demo PHP
===============

This application is designed to demo the workflow between OAuth2.0 Clients and Servers.

If this is your first time here, try [experimenting with the live demo](http://brentertainment.com/oauth2/) to get a better feel for OAuth2.0 flows.

> This library is running the [OAuth2 Server](https://github.com/bshaffer/oauth2-server-php) PHP library.

Installation
------------

Use [Composer](http://getcomposer.org/) to install this application:

    $ git clone git://github.com/bshaffer/oauth2-demo-php.git
    $ cd oauth2-demo-php
    $ curl -s http://getcomposer.org/installer | php
    $ ./composer.phar install

### WebHost Configuration

#### Configure a Web Server

Silex requires you to [configure your web server](http://silex.sensiolabs.org/doc/web_servers.html) to run it.

Be sure to run the command `$ chmod -R 777 data/` in the project root so that the web server can create the sqlite file.

#### Using PHP's built-in Web Server

You can use php's *built-in web server*, however, you will need to spin up **two instances** and specify one of
them in `data/parameters.json` in order to prevent the server from locking up. The client will issue a request
to the server, and because PHP's built-in web server is single-threaded, this will result in deadlock.

```
$ cd oauth2-demo-php
$ cp data/parameters.json.dist data/parameters.json
$ sed -i '' 's?"grant"?"http://localhost:8081/lockdin/token"?g' data/parameters.json
$ sed -i '' 's?"access"?"http://localhost:8081/lockdin/resource"?g' data/parameters.json
```

Now all you have to do is spin up two separate web servers in the `web` directory

```
$ cd web
$ php -S localhost:8080 & php -S localhost:8081
```

Browse to `http://localhost:8080` in your browser and you're all set!

What Does This App Do??
-----------------------

This application simulates the interaction between an **OAuth2 Client** (Demo App) and **OAuth2 Server** (Lock'd In). To get started,
access the Demo App homepage:

![Demo Application Homepage](http://brentertainment.com/other/screenshots/demoapp-authorize.png)

Clicking *Authorize* will send you to Lock'd In, which mimics a data provider (such as twitter, facebook, etc).
Lock'd In assumes you are already signed in, and asks if you'd like to grant the Demo app access
to your information:

![Lock'd In Authorization Request](http://brentertainment.com/other/screenshots/lockdin-authorize.png)

Once you click *Yes, I Authorize this Request*, you will be redirected back to Demo App with an `authorization
code`, which
[the client then exchanges](https://github.com/bshaffer/oauth2-server-demo/blob/master/src/OAuth2DemoClient/Controllers/ReceiveAuthorizationCode.php)
for an Access Token.  Demo App then makes another call to the Lock'd In APIs and uses the Access Token to retrieve
the data on your behalf.

If all is successful, your data from Lock'd In will be displayed on the final page:

![Demo Application Granted](http://brentertainment.com/other/screenshots/demoapp-granted.png)

The OAuth2 Client can be used to test *ANY* OAuth2.0 server, and can be configured to do so using the
the configuration file defined [below](#test-your-own-oauth2-server).

The OAuth2 Server
-----------------

The OAuth2 Server is [created](https://github.com/bshaffer/oauth2-demo-php/blob/master/src/OAuth2Demo/Server/Server.php) (see the `setup` method) and then used in the [Controller Classes](https://github.com/bshaffer/oauth2-demo-php/blob/master/src/OAuth2Demo/Server/Controllers), which implement
the following endpoints:

   * [/authorize](https://github.com/bshaffer/oauth2-demo-php/blob/master/src/OAuth2Demo/Server/Controllers/Authorize.php) - endpoint which grants the Demo App an `authorization code`
   * [/token](https://github.com/bshaffer/oauth2-demo-php/blob/master/src/OAuth2Demo/Server/Controllers/Token.php) - endpoint which grants the Demo App an `access_token` when supplied with the authorization code above
   * [/resource](https://github.com/bshaffer/oauth2-demo-php/blob/master/src/OAuth2Demo/Server/Controllers/Resource.php) - endpoint which grants the Demo App access to your protected resources (in this case, your friends) when supplied the access token above

These are the three main functions of the OAuth2 server (authorize the user, grant the user tokens, and validate api calls).  When you write your OAuth2-compatible servers, your interface will be similar.

> Note: the above urls are prefixed with `/server` to namespace the application.

Test Your Own OAuth2 Server!
----------------------------

You can test this application against your own OAuth application with ease.  Just copy over the `parameters.json.dist` file to `parameters.json`:

    $ cd /path/to/oauth2-demo-php
    $ cp data/parameters.json.dist data/parameters.json

Open the parameters.json file, and notice the default configuration:

    {
      "client_id": "demoapp",
      "client_secret": "demopass",
      "token_route": "grant",
      "authorize_route": "authorize",
      "resource_route": "access",
      "resource_method": "GET",
      "resource_params": {},
      "curl_options": {}
    }

This is the configuration for the default `Lock'd In` OAuth2 server.  To test against your own, change those parameters to fit the api server
you want to test against:

    {
      "client_id": "OAuth Demo Application",
      "client_secret": "a3b4b74330724a927bec",
      "token_route": "https://api.myapp.com/token",
      "authorize_route": "https://myapp.com/authorize",
      "resource_route": "https://api.myapp.com/profile",
      "resource_method": "POST",
      "resource_params": { "debug": true },
      "curl_options": { "http_port": 443, "verifyssl": false }
    }

The above example uses a new client to authenticate against a fictional oauth server at `myapp.com`.
This is very useful when testing your application in production

>  Note: The curl options are set to ignore an SSL certificate, and the `resource_params` define a fictional debug parameter.
>  These are not required for your APIs, but is meant as an example what can be done with the configuration

###Test in multiple environments

In addition, you can create multiple environments using the parameters.json file, and switch between them:

    {
        "LockdIn": {
          "client_id": "demoapp",
          "client_secret": "demopass",
          "token_route": "grant",
          "authorize_route": "authorize",
          "resource_route": "access",
          "resource_method": "GET",
          "resource_params": {},
          "curl_options": {}
        },
        "My App": {
          "client_id": "OAuth Demo Application",
          "client_secret": "a3b4b74330724a927bec",
          "token_route": "https://api.myapp.com/token",
          "authorize_route": "https://myapp.com/authorize",
          "resource_route": "https://api.myapp.com/profile",
          "resource_method": "POST",
          "resource_params": { "debug": true },
          "curl_options": { "http_port": 443, "verifyssl": false }
        }
    }

This will provide a dropdown at the top which will allow you to switch environments and test multiple OAuth servers

![Demo Application With Environment Select](http://brentertainment.com/other/screenshots/demoapp-environment-select.png)

Contact
-------

Please contact Brent Shaffer (bshafs \<at\> gmail \<dot\> com) for more information
