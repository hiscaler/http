<?php

namespace yadjet\http;

/**
 * Http class
 *
 * @author hiscaler <hiscaler@gmail.com>
 */
class Http
{

    /**
     * Access endpoint url.
     * @var string
     */
    public $endpointUrl;

    /**
     * Auth user id, if need.
     * @var string
     */
    public $authUserId;

    /**
     * Auth user password, if need.
     * @var strubg
     */
    public $authPassword;

    /**
     * Default headers
     * @var array
     */
    public $httpHeaders = array(
        'Accept: application/json',
        'Content-Type: application/json',
    );

    /**
     * If set it to true, will output the log message.
     * @var boolean
     */
    public $debug = false;

    public function __construct($endpointUrl, $authUserId = null, $authPassword = null)
    {
        $this->endpointUrl = $endpointUrl;
        $this->authUserId = $authUserId;
        $this->authPassword = $authPassword;
    }

    /**
     * Output log
     * 
     * @param string $url
     * @param string $method
     * @param array $data
     */
    private function _outputLog($url, $method, $data = array())
    {
        $line = str_repeat('#', 80) . "</br>";
        echo $line;
        echo strtoupper($method) . ": $url" . "</br>";
        if ($data) {
            if (is_string($data)) {
                echo $data . "</br>";
            } else {
                var_dump($data);
            }
        }
        echo $line;
    }

    private function _parseCurlResponse($status, $content)
    {
        if ($content === false) {
            return $status;
        } else {
            return json_decode($content, true);
        }
    }

    /**
     *  GET Request
     * 
     * @param string $url
     * @param mixed $params
     * @return boolean
     */
    public function get($url, $params = null)
    {
        $url = $this->endpointUrl . $url;
        $queryString = '';
        if (is_string($params)) {
            $queryString = $params;
        } elseif (is_array($params) && $params) {
            $queryString = http_build_query($params);
        }
        if (!empty($queryString)) {
            if (strpos($url, '?')) {
                $url = "{$url}&{$queryString}";
            } else {
                $url = "{$url}?{$queryString}";
            }
        }
        $curl = curl_init();
        if (stripos($url, 'https://') !== false) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->httpHeaders);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!empty($this->authUserId) && !empty($this->authPassword)) {
            curl_setopt($curl, CURLOPT_USERPWD, "{$this->authUserId}:{$this->authPassword}");
        }
        $content = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);

        if ($this->debug) {
            $this->_outputLog($url, __FUNCTION__);
        }
        return $this->_parseCurlResponse($status, $content);
    }

    /**
     * POST Request
     * 
     * @param string $url
     * @param array $params
     * @param boolean $isFile
     * @return string content
     */
    public function post($url, $params, $isFile = false)
    {
        $url = $this->endpointUrl . $url;
        $params = $isFile ? $params : (is_string($params) ? $params : http_build_query($params));
        $curl = curl_init();
        if (stripos($url, 'https://') !== false) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->httpHeaders);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!empty($this->authUserId) && !empty($this->authPassword)) {
            curl_setopt($curl, CURLOPT_USERPWD, "{$this->authUserId}:{$this->authPassword}");
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $content = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);

        if ($this->debug) {
            $this->_outputLog($url, __FUNCTION__, array(
                'params' => $params,
                'isFile' => $isFile,
            ));
        }

        return $this->_parseCurlResponse($status, $content);
    }

    /**
     * PUT Request
     * 
     * @param string $url
     * @param array $data
     * @return string
     */
    public function put($url, $data = array())
    {
        $url = $this->endpointUrl . $url;
        $curl = curl_init();
        if (stripos($url, 'https://') !== false) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $putData = tmpfile();
        fwrite($putData, is_array($data) ? json_encode($data) : $data);
        fseek($putData, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->httpHeaders);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (!empty($this->authUserId) && !empty($this->authPassword)) {
            curl_setopt($curl, CURLOPT_USERPWD, "{$this->authUserId}:{$this->authPassword}");
        }
        curl_setopt($curl, CURLOPT_PUT, true);
        curl_setopt($curl, CURLOPT_INFILE, $putData);
        $content = curl_exec($curl);
        $status = curl_getinfo($curl);
        fclose($putData);
        curl_close($curl);

        if ($this->debug) {
            $this->_outputLog($url, __FUNCTION__, array(
                'data' => $data,
            ));
        }

        return $this->_parseCurlResponse($status, $content);
    }

    /**
     * Delete Request
     * 
     * @param string $url
     * @return string
     */
    public function delete($url)
    {
        $url = $this->endpointUrl . $url;
        $curl = curl_init();
        if (stripos($url, 'https://') !== false) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->httpHeaders);
        if (!empty($this->authUserId) && !empty($this->authPassword)) {
            curl_setopt($curl, CURLOPT_USERPWD, "{$this->authUserId}:{$this->authPassword}");
        }
        $content = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);

        if ($this->debug) {
            $this->_outputLog($url, __FUNCTION__);
        }

        return $this->_parseCurlResponse($status, $content);
    }

}
