<?php
/**
 * @package Magento Module.
 * @author: A.A.Treitjak
 * @copyright: 2012 - 2014 BelVG.com
 */


namespace Codeception\Util\Connector;


class Magento extends \Symfony\Component\BrowserKit\Client
{
    /**
     * @var \Mage_Core_Model_App
     */
    protected $bootstrap;

    /**
     * @var  \Mage_Core_Controller_Request_Http
     */
    protected $mageRequest;

    /**
     * @var array $params application run parameters
     */
    protected $params = array();

    public function setBootstrap($bootstrap) {
        $this->bootstrap = $bootstrap;
    }

    public function setParams($params = array()) {
        $this->params = $params;
    }

    public function doRequest($request) {

        $mageRequest = $this->bootstrap->getRequest();
        $this->setCookies($request->getCookies());
        $mageRequest->setParams($request->getParameters());
        $mageRequest->setRequestUri(str_replace('http://localhost','',$request->getUri()));
        $this->setHeaders($mageRequest, $request->getServer());
        $_FILES = $request->getFiles();
        $_SERVER = array_merge($_SERVER, $request->getServer());

        $mageResponse = $this->bootstrap->getResponse();

        $this->bootstrap->run($this->params);
        $this->mageRequest = $mageRequest;

        $response = new \Symfony\Component\BrowserKit\Response($mageResponse->getBody(), $mageResponse->getHttpResponseCode(), $mageResponse->getHeaders());
        return $response;
    }

    /**
     * @return \Mage_Core_Controller_Request_Http
     */
    public function getMageRequest() {
        return $this->mageRequest;
    }

    protected function setCookies($cookies)
    {
        $_COOKIE += $cookies;
    }

    protected function setHeaders($headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    protected function setHeader($name, $value)
    {
        $name = $this->headerName($name);
        $this->setServer('HTTP_' . $name, $value);
    }

    protected function headerName($name)
    {
        return strtr(strtoupper($name), '-', '_');
    }

    protected function setServer($name, $value)
    {
        $_SERVER[$name] = $value;
    }
} 