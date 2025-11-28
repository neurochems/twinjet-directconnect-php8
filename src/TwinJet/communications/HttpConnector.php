<?php

namespace TwinJet\communications;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\ConnectException;
use \GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use TwinJet\ApiException;
use TwinJet\ConnectorException;

/**
 * HTTPConnector class to handle HTTP requests to the REST API
 *
 * @author George Brownlee
 */
class HttpConnector {

    /**
     * processRequest() function - Public facing function to send a request to an endpoint.
     *
     * @param string $method HTTP method to use (defaults to GET if $data==null; defaults to PUT if $data!=null)
     * @param string $uri Incoming API Endpoint
     * @param array|null $data Data for POST requests, not needed for GETs
     * @return array Parsed API response from private request method
     * @throws ApiException
     * @throws ConnectorException
     * @access    public
     */
    public function processRequest($method, $uri, $data)
    {
        return $this->request($method, $uri, $data);
    }


    /**
     * request() function - Internal function to send a request to an endpoint.
     *
     * @param	string $method HTTP method to use (defaults to GET if $data==null; defaults to POST if $data!=null)
     * @param	string $uri Incoming API Endpoint
     * @param	array|null $data Data for POST requests, not needed for GETs
     * @access	private
     * @return	array Parsed API response
     *
     * @throws ApiException
     * @throws ConnectorException
     */
    private function request($method, $uri, $data = NULL)
    {
        $client = new Client();

        $response = null;
        $options = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'verify' => 'vendor\composer\ca-bundle\res\cacert.pem'  // updating guzzle format 2025
        );

        if ( !is_null($data) )
        {
            $options['body'] = json_encode($data);
        }
        else
        {
            $method = 'GET';
        }

        try
        {
            $request = new Request($method, $uri);                 // updating guzzle format 2025
            $response = $client->sendAsync($request, $options)->wait();
        }
        catch (RequestException | ConnectException $e)
        {
            throw new ConnectorException('Unexpected Guzzle error ' . $e->getMessage(), 0);
        }

        if (false === $response)
        {
            throw new ConnectorException("No response was received", 0);
        }

        if (is_null($response))
        {
            throw new ConnectorException('Unexpected response format', 0);
        }

        $res = json_decode($response->getBody(),true);

        // Check for HTTP error codes
        $statusCode = $response->getStatusCode();
        if( !($statusCode >= 200 && $statusCode < 300) )
        {
            throw new ApiException("", $statusCode);
        }

        return $res;
    }

}