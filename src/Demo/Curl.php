<?php

namespace Demo;

class Curl
{
    /**
    * Send a request to the server, receive a response
    *
    * @param  string   $apiPath       Request API path
    * @param  array    $parameters    Parameters
    * @param  string   $httpMethod    HTTP method to use
    *
    * @return string   HTTP response
    */
    public function request($url, array $parameters = array(), $httpMethod = 'GET', array $options = array())
    {
        $curlOptions = array();
        $headers = array();

        if ('POST' === $httpMethod) {
            $curlOptions += array(
                CURLOPT_POST  => true,
            );
        }
        elseif ('PUT' === $httpMethod) {
            $curlOptions += array(
                CURLOPT_POST  => true, // This is so cURL doesn't strip CURLOPT_POSTFIELDS
                CURLOPT_CUSTOMREQUEST => 'PUT',
            );
        }
        elseif ('DELETE' === $httpMethod) {
            $curlOptions += array(
                CURLOPT_CUSTOMREQUEST => 'DELETE',
            );
        }

        if (!empty($parameters))
        {
            if('GET' === $httpMethod)
            {
                $queryString = utf8_encode($this->buildQuery($parameters));
                $url .= '?' . $queryString;
            }
            else
            {
                $curlOptions += array(
                    CURLOPT_POSTFIELDS  => json_encode($parameters)
                );
                $headers[] = 'Content-Type: application/json';
            }
        } else {
            $headers[] = 'Content-Length: 0';
        }

        $this->debug('send '.$httpMethod.' request: '.$url);

        $curlOptions += array(
            CURLOPT_URL             => $url,
            CURLOPT_PORT            => $options['http_port'],
            CURLOPT_USERAGENT       => $options['user_agent'],
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => $this->options['timeout'],
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_SSL_VERIFYPEER  => !isset($this->options['verifyssl']) || $this->options['verifyssl'],
        );

        if (isset($this->options['curlopts']) && is_array($this->options['curlopts'])) {
            $curlOptions += $this->options['curlopts'];
        }

        if ($options['proxy']) {
            $curlOptions[CURLOPT_PROXY] = $options['proxy'];
        }

        $response = $this->doCurlCall($curlOptions);

        return $response;
    }

    /**
     * Get a JSON response and transform it to a PHP array
     *
     * @return  array   the response
     */
    protected function decodeResponse($response)
    {
        // "false" means a failed curl request
        if (false === $response['response']) {
            $this->debug(print_r($response, true));
            return false;
        }
        return parent::decodeResponse($response);
    }

    protected function doCurlCall(array $curlOptions)
    {
        $curl = curl_init();

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $headers = curl_getinfo($curl);
        $errorNumber = curl_errno($curl);
        $errorMessage = curl_error($curl);

        curl_close($curl);

        return compact('response', 'headers', 'errorNumber', 'errorMessage');
    }

    protected function buildQuery($parameters)
    {
        return http_build_query($parameters, '', '&');
    }

    protected function debug($message)
    {
        if($this->options['debug'])
        {
            print $message."\n";
        }
    }
}
