OAuth2 Server Demo
==================

This is a small app running the [OAuth2 Server](https://github.com/bshaffer/oauth2-server-php) PHP library.

**Before reading any further!!**: Please [experiment with the live demo](http://brentertainment.com/oauth2/).

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

The OAuth2 Server
-----------------

The **first thing you should do** is take a look at how the OAuth2 Server is created and configured in [src/OAuth2Demo/Server/Server.php](https://github.com/bshaffer/oauth2-server-demo/blob/master/src/OAuth2Demo/Server/Server.php).  The **second thing you should do** is look at the code to implement the [Controller Classes](https://github.com/bshaffer/oauth2-server-demo/blob/master/src/OAuth2Demo/Server/Controllers), which implement
the following endpoints:

   * [/authorize](https://github.com/bshaffer/oauth2-server-demo/blob/master/src/OAuth2Demo/Server/Controllers/Authorize.php) - endpoint which grants the Demo App an `authorization code`
   * [/token](https://github.com/bshaffer/oauth2-server-demo/blob/master/src/OAuth2Demo/Server/Controllers/Token.php) - endpoint which grants the Demo App an `access_token` when supplied with the authorization code above
   * [/resource](https://github.com/bshaffer/oauth2-server-demo/blob/master/src/OAuth2Demo/Server/Controllers/Resource.php) - endpoint which grants the Demo App access to your protected resources (in this case, your friends) when supplied the access token above

These are the three main functions of the OAuth2 server (authorize the user, grant the user tokens, and validate api calls).  When you write your OAuth2-compatible servers, your interface will be similar.

> Note: the above urls are prefixed with `/server` to namespace the application.

Test Your Own OAuth2 Server!
----------------------------

You can test this application against your own OAuth application with ease.  Just copy over the `parameters.json.dist` file to `parameters.json`:

    $ cd /path/to/oauth2-server-demo
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
      "resource_params": { "debug": true }
      "curl_options": { "http_port": 443, "verifyssl": false }
    }

The above example uses a new client to authenticate against a fictional oauth server at `myapp.com`.
This is very useful when testing your application in produciton

>  Note: The curl options are set to ignore an SSL certificate, and the `resource_params` define a fictional debug parameter.
>  These are not required for your APIs, but is meant as an example what can be done with the configuraiton

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
