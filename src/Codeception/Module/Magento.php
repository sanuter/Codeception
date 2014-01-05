<?php
/**
 * @package Magento Module.
 * @author: A.A.Treitjak
 * @copyright: 2012 - 2014 BelVG.com
 */


namespace Codeception\Module;


class Magento extends \Codeception\Util\Framework implements \Codeception\Util\FrameworkInterface
{
    protected $config = array(
        'code' => '',
        'type' => 'store',
        'options' => array(),
        'app_path' => 'app',
        'host' => '',
        'rollback' => TRUE
    );

    /**
     * @var \Varien_Db_Adapter_Pdo_Mysql
     */
    public $db;

    /**
     * @var \Mage_Core_Model_App
     */
    public $bootstrap;

    /**
     * @var \Codeception\Util\Connector\Magento
     */
    public $client;

    public function _initialize() {
        // Include Mage file by detecting app root
        require_once getcwd(). DIRECTORY_SEPARATOR. $this->config['app_path'] . DIRECTORY_SEPARATOR . 'Mage.php';
        $this->client = new \Codeception\Util\Connector\Magento();

        if ($this->config['host']) {
            $this->client->setServerParameter('HTTP_HOST', $this->config['host']);
        }

        $this->client->setParams($this->config['options']);
    }

    public function _before(\Codeception\TestCase $test) {

        $this->bootstrap = \Mage::app()->init($this->config['code'], $this->config['type'], $this->config['options']);
        $this->client->setBootstrap($this->bootstrap);

        $db = \Mage::getSingleton('core/resource')->getConnection('core_write');

        if ($db instanceof \Varien_Db_Adapter_Pdo_Mysql) {
            $this->db = $db;
            if ($this->config['rollback']) {
                $this->db->beginTransaction();
            }
        }
    }

    public function _after(\Codeception\TestCase $test) {
        $_SESSION = array();
        $_GET     = array();
        $_POST    = array();
        $_COOKIE  = array();

        if ($this->config['rollback']) {
            $this->db->rollBack();
        }
    }

    protected function debugResponse()
    {
        $this->debugSection('Server', var_export($_SERVER, TRUE));
        $this->debugSection('Session', var_export($_SESSION, TRUE));
        $this->debugSection('GET', var_export($_GET, TRUE));
        $this->debugSection('POST', var_export($_POST, TRUE));
        $this->debugSection('Cookie', var_export($_COOKIE, TRUE));
        $this->debugSection('Files', var_export($_FILES, TRUE));

        if ($this->db) {
            $profiler = $this->db->getProfiler();
            $queries = $profiler->getTotalNumQueries() - $this->queries;
            $time = $profiler->getTotalElapsedSecs() - $this->time;
            $this->debugSection('Db',$queries.' queries');
            $this->debugSection('Time',round($time,2).' secs taken');
            $this->time = $profiler->getTotalElapsedSecs();
            $this->queries = $profiler->getTotalNumQueries();
        }
    }
} 