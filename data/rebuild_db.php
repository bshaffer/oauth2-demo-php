<?php

// determine where the sqlite DB will go
$dir = __DIR__.'/oauth.sqlite';

// remove sqlite file if it exists
if (file_exists($dir)) {
    unlink($dir);
}

// rebuild the DB
$db = new PDO(sprintf('sqlite://%s', $dir));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE oauth_clients (client_id TEXT, client_secret TEXT, redirect_uri TEXT)');
$db->exec('CREATE TABLE oauth_access_tokens (access_token TEXT, client_id TEXT, user_id TEXT, expires TIMESTAMP, scope TEXT)');

// add test data
$db->exec('INSERT INTO oauth_clients (client_id, client_secret) VALUES ("oauth_test_client", "testpass")');
$db->exec('INSERT INTO oauth_access_tokens (access_token, client_id) VALUES ("testtoken", "Some Client")');