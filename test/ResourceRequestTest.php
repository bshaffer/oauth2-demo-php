<?php

//namespace Comunio\OAuth2;

/**
 * @author Marovelo
 */
class ResourceRequestTest extends \PHPUnit_Framework_TestCase {

    // adjust these adresses to your local oauth server
    const TOKEN_REQUEST_URL = "http://oauth.demo.local/lockdin/token";
    const RESOURCE_REQUEST_URL = "http://oauth.demo.local/lockdin/resource";

    /** @test */
    public function resourceRequestFails(){
        /* ------------ obtain Access Token -------------------------- */
        $url = self::TOKEN_REQUEST_URL;
        $post = true;
        $postfields = "grant_type=password&client_id=demoapp&client_secret=demopass&username=demouser&password=testpass" .
            "&scope=LOW_PRIVILEGED_SCOPE";
        $user="demoapp";
        $password="demopass";
        $https = false;

        $response = $this->request ($url, $post, $postfields, $user, $password, $https);
        $jsonResponse = json_decode($response["content"]);

        /* ------------ Resource Request -------------------------- */
        $url = self::RESOURCE_REQUEST_URL . "?access_token=" . $jsonResponse->access_token . "&required_scope=HIGH_PRIVILEGED_SCOPE";
        $post = false;
        $https = false;

        $response = $this->request ($url, $post, null, null, null, $https);
        $result = $response["httpCode"];

        $expected = 403;
        $this->assertEquals($expected, $result);
    }

    public function request($url, $post = false, $postFields = "", $user = null, $password = null, $https = true){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        if (isset($user)){
            curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $password);
        }

        if ($https) {
            // HTTPS settings
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return array("httpCode" => $httpCode, "content" => $response);
    }
}