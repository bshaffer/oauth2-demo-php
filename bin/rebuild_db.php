#!/usr/bin/env php
<?php

// determine where the sqlite DB will go
$dir = dirname(__DIR__) . '/data/oauth.sqlite';

// remove sqlite file if it exists
if (file_exists($dir)) {
    unlink($dir);
}

// rebuild the DB
$db = new PDO(sprintf('sqlite://%s', $dir));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE oauth_clients (client_id TEXT, client_secret TEXT, redirect_uri TEXT)');
$db->exec('CREATE TABLE oauth_access_tokens (access_token TEXT, client_id TEXT, user_id TEXT, expires TIMESTAMP, scope TEXT)');
$db->exec('CREATE TABLE oauth_authorization_codes (authorization_code TEXT, client_id TEXT, user_id TEXT, redirect_uri TEXT, expires TIMESTAMP, scope TEXT)');

$db->exec('CREATE TABLE oauth_refresh_tokens (refresh_token TEXT, client_id TEXT, user_id TEXT, expires TIMESTAMP, scope TEXT)');
$db->exec('INSERT INTO oauth_clients (client_id, client_secret, redirect_uri) VALUES ("demoapp", "demopass", "http://localhost/demo/authorized")');

chmod($dir, 0777);
// $db->exec('INSERT INTO oauth_access_tokens (access_token, client_id) VALUES ("testtoken", "Some Client")');
// $db->exec('INSERT INTO oauth_authorization_codes (authorization_code, client_id) VALUES ("testcode", "Some Client")');
