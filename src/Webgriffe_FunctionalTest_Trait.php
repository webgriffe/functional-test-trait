<?php

use Igorw\CgiHttpKernel\CgiHttpKernel;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\Client;

trait Webgriffe_FunctionalTest_Trait
{
    /**
     * @param Mage_Core_Model_Website $website
     * @param bool $setXdebugCookie
     * @param string $frontController
     * @return Client
     * @throws \RuntimeException
     * @throws Mage_Core_Exception
     */
    protected static function createClient(
        Mage_Core_Model_Website $website = null,
        $setXdebugCookie = false,
        $frontController = 'index.php'
    ) {
        $phpCgiBin = (string)Mage::getConfig()->getNode('phpunit/functional/php_cgi_bin') ?: 'php-cgi';
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $server = [];
        if ($website) {
            $baseUrl = $website->getDefaultStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $server['MAGE_RUN_TYPE'] = 'website';
            $server['MAGE_RUN_CODE'] = $website->getCode();
        }
        $server['HTTP_HOST'] = parse_url($baseUrl, PHP_URL_HOST);
        $server['MAGE_LOAD_ECOMDEV_PHPUNIT_CONFIG'] = true;
        $server['MAGE_IS_DEVELOPER_MODE'] = 1;
        $server['MAGE_ENVIRONMENT'] = $_SERVER['MAGE_ENVIRONMENT'];
        $cgiHttpKernel = new CgiHttpKernel(Mage::getBaseDir(), $frontController, $phpCgiBin);
        if ($setXdebugCookie) {
            $cgiHttpKernel = new CgiHttpKernel(Mage::getBaseDir(), $frontController, $phpCgiBin, null);
        }
        $client = new Client($cgiHttpKernel, $server);
        if ($setXdebugCookie) {
            $xdebugSession = (string)Mage::getConfig()->getNode('phpunit/functional/xdebug_session');
            if (!$xdebugSession) {
                throw new \RuntimeException(
                    'Cannot set xDebug cookie, its value is not configured. Please, define an "XDEBUG_SESSION" ' .
                    'cookie value, according to your IDE settings, at config path "phpunit/functional/xdebug_session" '.
                    'in your app/etc/local.xml.'
                );
            }
            $client->getCookieJar()->set(new Cookie('XDEBUG_SESSION', $xdebugSession));
        }
        return $client;
    }

    /**
     * @param Client $client
     * @throws \RuntimeException
     */
    protected static function openResponseInBrowser(Client $client)
    {
        $openCommand = (string)Mage::getConfig()->getNode('phpunit/functional/open_browser_command');
        if (!$openCommand) {
            throw new \RuntimeException(
                'Cannot open response in browser. Open command is not configured. Please define a browser open ' .
                'command at config path "phpunit/functional/open_browser_command" in your app/etc/local.xml file. ' .
                'Command must be like "open \'%s\'" (where "%s" will be replaced with the file which contains the ' .
                'response to open in browser).'
            );
        }
        $tmpFile = tempnam(sys_get_temp_dir(), 'response_') . '.html';
        file_put_contents($tmpFile, $client->getResponse()->getContent());
        exec(sprintf($openCommand, $tmpFile));
    }
}
