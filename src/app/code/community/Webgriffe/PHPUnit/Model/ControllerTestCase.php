<?php


class Webgriffe_PHPUnit_Model_ControllerTestCase extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @param bool $setXdebugCookie
     * @return \Symfony\Component\HttpKernel\Client
     */
    protected static function createClient($setXdebugCookie = false)
    {
        $client = new \Symfony\Component\HttpKernel\Client(
            new \Igorw\CgiHttpKernel\CgiHttpKernel(Mage::getBaseDir(), 'index.php'),
            ['HTTP_HOST' => parse_url(Mage::getBaseUrl(), PHP_URL_HOST)]
        );
        if ($setXdebugCookie) {
            $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie('XDEBUG_SESSION', 'PHPSTORM'));
        }
        return $client;
    }

    protected static function openResponseInBrowser(\Symfony\Component\HttpKernel\Client $client)
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
