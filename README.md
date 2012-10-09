OAuth2 Server Demo
==================

This is a small app running the [OAuth2 Server](https://github.com/bshaffer/oauth2-server-php) PHP library

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

Usage
-----

This is a very simple server implemententation for oauth2.  This exposes the following server endpoints:

   * `/authorize` - endpoint to receive an Authorization Code or an Implicit Token Grant
   * `/grant`     - endpoint to receive an Access Token by requesting one of the valid Grant Types
   * `/access`    - endpoint to access a protected resource using an access token

These are the three main functions of an OAuth server.
