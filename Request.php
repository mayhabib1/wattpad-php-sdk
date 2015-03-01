<?php
/**
 * Copyright 2015 WP Technology Inc.
 */

namespace WattpadSdk;

class Request
{
    private $accessToken, $apiKey;

    /**
     * Constructor for a Wattpad\Request object
     *
     * @param string    $apiKey         The client application's API key obtained from developer.wattpad.com
     * @param string    $accessToken    The Wattpad user's access token for the client application
     */
    public function __constructor($apiKey, $accessToken = null)
    {
        $this->accessToken = $accessToken;
        $this->apiKey = $apiKey;
    }
} 