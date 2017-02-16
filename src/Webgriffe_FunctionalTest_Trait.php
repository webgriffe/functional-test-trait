<?php

use Igorw\CgiHttpKernel\CgiHttpKernel;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\Client;

trait Webgriffe_FunctionalTest_Trait
{
    /**
     * @param bool $setXdebugCookie
     * @return Client
     */
    protected static function createClient($setXdebugCookie = false)
    {
        $client = new Client(
            new CgiHttpKernel(Mage::getBaseDir(), 'index.php'),
            ['HTTP_HOST' => parse_url(Mage::getBaseUrl(), PHP_URL_HOST)]
        );
        if ($setXdebugCookie) {
            $client->getCookieJar()->set(new Cookie('XDEBUG_SESSION', 'PHPSTORM'));
        }
        return $client;
    }

    /**
     * @param Client $client
     * @throws \RuntimeException
     */
    protected static function openResponseInBrowser(Client $client)
    {
        $openCommand = (string)Mage::getConfig()->getNode('phpunit/browser/open_command');
        if (!$openCommand) {
            throw new \RuntimeException(
                'Cannot open response in browser. Open command is not configured. Please define a browser open ' .
                'command at config path "phpunit/browser/open_command" in your app/etc/local.xml file. Command ' .
                'must be like "open \'%s\'" (where "%s" will be replaced with the file which contains the response ' .
                'to open in browser).'
            );
        }
        $tmpFile = tempnam(sys_get_temp_dir(), 'response_') . '.html';
        file_put_contents($tmpFile, $client->getResponse()->getContent());
        exec(sprintf($openCommand, $tmpFile));
    }
}
