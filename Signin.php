<?php
/**
 * Copyright 2015 WP Technology Inc.
 */

namespace WattpadSdk;

include_once("exceptions/signinException.php");

class Signin
{
    private $apiKey, $secret, $redirectUri, $scope;

    const OAUTH_URL = "https://www.wattpad.com/oauth/";

    /**
     * Constructor for a Wattpad\Signin object
     *
     * @param string $apiKey        The client application's API key obtained from developer.wattpad.com
     * @param string $secret        The client application's secret obtained from developer.wattpad.com
     * @param string $redirectUri   The client application's URI to hit when the access token is granted
     * @param string $scope         The scope of access the client application is requesting from the user - accepted values are "read" or "write"
     */
    public function __constructor($apiKey, $secret, $redirectUri, $scope = "read")
    {
        $this->apiKey = $apiKey;
        $this->secret = $secret;
        $this->redirectUri = $redirectUri;
        $this->scope = $scope;
    }

    /**
     * Gets the Wattpad OAuth authorize URL
     * @return string   The Wattpad Authorization Code URL
     * @throws Exceptions\SigninException
     */
    public function getAuthorizeUrl()
    {
        if (empty($this->apiKey)) {
            throw new Exceptions\SigninException("Missing client application API key");
        } else if (empty($this->redirectUri)) {
            throw new Exceptions\SigninException("Missing client application redirect URI");
        }

        $urlParameters = array(
            "apiKey" => $this->apiKey,
            "redirectUri" => urlencode($this->redirectUri),
            "scope" => $this->scope
        );

        return self::OAUTH_URL."code?".http_build_query($urlParameters);
    }

    /**
     * Gets the Wattpad URL to get user access token
     * @return string   The Wattpad User Access Token URL
     * @throws Exceptions\SigninException
     */
    private function getAccessTokenUrl($authorizationCode)
    {
        if (empty($this->apiKey)) {
            throw new Exceptions\SigninException("Missing client application API key");
        } else if (empty($this->secret)) {
            throw new Exceptions\SigninException("Missing client application secret");
        } else if (empty($this->redirectUri)) {
            throw new Exceptions\SigninException("Missing client application redirect URI");
        } else if (empty($authorizationCode)) {
            throw new Exceptions\SigninException("Missing user authorization code");
        }

        $urlParameters = array(
            "grantType" => "authorizationCode",
            "apiKey" => $this->apiKey,
            "secret" => $this->secret,
            "authCode" => $authorizationCode,
            "redirectUri" => urlencode($this->redirectUri)
        );

        return self::OAUTH_URL."token?".http_build_query($urlParameters);
    }

    /**
     * Get the Wattpad user access token given the authorization code
     *
     * @param string $authorizationCode
     * @return array("token" => accessToken, "username" => wattpadUsername)
     * @throws Exceptions\SigninException
     */
    public function getAccessToken($authorizationCode)
    {
        try {
            $url = $this->getAccessTokenUrl($authorizationCode);
        } catch (Exceptions\SigninException $e) {
            throw $e;
        }

        $urlParts = explode("?", $url);

        // create curl resource
        $ch = curl_init($urlParts[0]);

        // set url
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $urlParts[1]);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        $error_number = curl_errno($ch);
        $error_message = curl_error($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        if ($error_number) {
            throw new Exceptions\SigninException("cURL error #".$error_number.": ".$error_message);
        }
        $response = json_decode($output, true);

        if (isset($response['error'])) {
            throw new Exceptions\SigninException($response['error']);
        }

        return $response['auth'];
    }
} 