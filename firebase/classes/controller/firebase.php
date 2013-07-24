<?php
/**
 * Fuelphp-Firebase Controller
 *
 *  -- Based on firebase-php project: https://github.com/ktamas77/firebase-php
 *
 *  Credits:
 *  @ktama77
 *  @craigrusso
 *  @mintao
 *
 */

namespace Firebase;

class Controller_Firebase extends \Controller
{
    protected $_baseURI;
    protected $_timeout;
    protected $_token;

    public function before()
    {
        // Check for CURL Extension
        if ( ! extension_loaded('curl')) {
            throw new \Exception('PHP Extension CURL must be loaded to use Firebase.');
        }

        // Load from config file "firebase.php"
        \Config::load('firebase');
        if (\Config::get('firebase_url') === null) {
            throw new \Exception('You must configure the "firebase_url" in config/firebase.php');
        }

        $this->_baseURI = (substr(\Config::get('firebase_url'), -1) == '/' ? \Config::get('firebase_url') : \Config::get('firebase_url').'/');
        $this->_timeout = (\Config::get('firebase_timeout') === null) ? 10 : \Config::get('firebase_timeout');
        $this->_token   = (\Config::get('firebase_token') === null) ? "" : \Config::get('firebase_token');
    }

    /**
     * Primary controller method
     *  -- Routed for all actions
     *
     * @param String $action
     * @param Accepts remaining arguments
     *
     * @return JSON $response
     */
    public function action_index($action)
    {
        $params = func_get_args();
        switch($action) {
            case "get":
            case "set":
            case "push":
            case "update":
            case "delete":
                $path = $this->getUriFrom(2);
                break;
            default:
                $action = 'get';
                $path = $this->getUriFrom(1);
        }

        switch($action) {
            case "get":
            case "delete":
                $response = $this->$action($path);
                break;
            case "set":
            case "push":
            case "update":
                if(\Request::is_hmvc()) {
                    $response = $this->$action($path, $this->getObjFrom($params));
                } else {
                    $response = null;
                    throw new \Exception('Cannot Set/Push/Update via GET!');
                }
                break;
        }

        if( ! \Request::is_hmvc())
        {
            echo $response;
        }
        else
        {
            return $response;
        }
    }

    /**
     * Returns remaining path after <action>
     *
     * @param Integer $index
     *
     * @return String URL
     */
    private function getUriFrom($index = 0)
    {
        $uri = "";
        $segments = \Uri::segments();
        for($seg = $index; $seg < sizeof($segments); $seg++) $uri.=$segments[$seg]."/";
        return $uri;
    }

    /**
     * Returns data object for set, push, update methods
     *
     * @param Array $args from action_index arguments
     *
     * @return Array final argument
     */
    private function getObjFrom($args)
    {
        return $args[sizeof(\Uri::segments())-1];
    }

    /**
     * Returns with the normalized JSON absolute path
     *
     * @param String $path to data
     */
    private function _getJsonPath($path)
    {
        $url = $this->_baseURI;
        $path = ltrim($path, '/');
        $auth = ($this->_token == '') ? '' : '?auth=' . $this->_token;
        return $url . $path . '.json' . $auth;
    }

    /**
     * Writing data into Firebase with a PUT request
     * HTTP 200: Ok
     *
     * @param String $path Path
     * @param Mixed  $data Data
     *
     * @return Array Response
     */
    public function set($path, $data)
    {
        return $this->_writeData($path, $data, 'PUT');
    }

    /**
     * Pushing data into Firebase with a POST request
     * HTTP 200: Ok
     *
     * @param String $path Path
     * @param Mixed  $data Data
     *
     * @return Array Response
     */
    public function push($path, $data)
    {
        return $this->_writeData($path, $data, 'POST');
    }

    /**
     * Updating data into Firebase with a PATH request
     * HTTP 200: Ok
     *
     * @param String $path Path
     * @param Mixed  $data Data
     *
     * @return Array Response
     */
    public function update($path, $data)
    {
        return $this->_writeData($path, $data, 'PATCH');
    }

    /**
     * Reading data from Firebase
     * HTTP 200: Ok
     *
     * @param String $path Path
     *
     * @return Array Response
     */
    public function get($path)
    {
        try {
            $ch = $this->_getCurlHandler($path, 'GET');
            $return = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            $return = null;
        }
        return $return;
    }

    /**
     * Deletes data from Firebase
     * HTTP 204: Ok
     *
     * @param type $path Path
     *
     * @return Array Response
     */
    public function delete($path)
    {
        try {
            $ch = $this->_getCurlHandler($path, 'DELETE');
            $return = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            $return = null;
        }
        return $return;
    }

    /**
     * Returns with Initialized CURL Handler
     *
     * @param String $mode Mode
     *
     * @return CURL Curl Handler
     */
    private function _getCurlHandler($path, $mode)
    {
        $url = $this->_getJsonPath($path);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mode);
        return $ch;
    }

    private function _writeData($path, $data, $method = 'PUT')
    {
        $jsonData = json_encode($data);
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        );
        try {
            $ch = $this->_getCurlHandler($path, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            $return = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            $return = null;
        }
        return $return;
    }

}
