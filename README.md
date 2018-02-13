Webgriffe Functional Test Trait
===============================

Trait which enables functional testing in Magento with [EcomDev_PHPUnit](https://github.com/EcomDev/EcomDev_PHPUnit) and using a [CgiHttpKernel](https://github.com/webgriffe/CgiHttpKernel).

Installation
------------

Import it using Composer (note, it's a dev dependency):

```
$ composer require --dev webgriffe/functional-test-trait
```
    
Usage
-----

The suggested usage of this trait is in combination with [EcomDev_PHPUnit](https://github.com/EcomDev/EcomDev_PHPUnit) and the [Webgriffe's Magento Config Extension](https://github.com/webgriffe/config-extension).
So, first of all, add these dependencies to your Magento project:

```
$ composer require --dev webgriffe/functional-test-trait ecomdev/ecomdev_phpunit
$ composer require webgriffe/config-extension
```	
	
Another suggested dependency is `symfony/css-selector`, which allows to select DOM elements using CSS selectors in your functional tests:

```
$ composer require --dev symfony/css-selector
```

Then setup your test suite as usual with EcomDev_PHPUnit.

Also you can add a `<testsuite>` node to your `phpunit.xml.dist` to group your functional tests in a dedicated test suite:

```xml
<testsuite name="My Project Functional Test Suite">
    <directory suffix="Test.php">tests</directory>
</testsuite>
``` 

Also, to avoid session issues, you should make sure that the `base_url` is always the same during tests setup and tests run. You can do this by setting the `MAGE_ENVIRONMENT` server variable through your `phpunit.xml.dist`:

```xml
<php>
    <server name="MAGE_ENVIRONMENT" value="test" />
</php>
```
And then set the `base_url` with the override configuration file for that environment (`app/etc/config-override.test.xml.dist`), with the content:

```xml
<?xml version="1.0"?>
<config>
    <default>
        <web>
            <secure>
                <base_url>http://url-functional.test/</base_url>
            </secure>
            <unsecure>
                <base_url>http://url-functional.test/</base_url>
            </unsecure>
        </web>
    </default>
</config>
```

Now you're ready to write your first Magento functional test. 
Put a test your `tests/` directory. For example, `HomepageTest.php`:

```php
<?php
	
class HomepageTest extends EcomDev_PHPUnit_Test_Case_Controller
{
    use Webgriffe_FunctionalTest_Trait;
	
    /**
     * @loadFixture category.yaml
     */
    public function testHome()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');
	
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains('Default welcome msg!', $crawler->filter('div.welcome-msg')->text());
        $this->assertCount(1, $crawler->filter('ul.nav.mega-menu'));
        $categoryLink = $crawler->filter('ul.nav.mega-menu')->filter('li')->eq(0)->filter('a');
        $this->assertCount(1, $categoryLink);
        $this->assertContains('My Category', $categoryLink->text());
	
        $crawler = $client->click($categoryLink->link());
	
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertContains('My Category', $crawler->filter('div.page-title h1')->text());
    }
}
```
It's important that your test extends the `EcomDev_PHPUnit_Test_Case_Controller` class to correctly load Magento's frontend area configuration.
	
As you may notice there is the `@loadFixture` annotation which loads data from `category.yaml` file located at `tests/HomepageTest/fixtures/category.yaml` which content could be:

```yaml
eav:
  catalog_category:
    - entity_id: 3
      name: My Category
      parent_id: 2
      path: 1/2/3
      level: 2
      url_key: my-category
      is_active: 1
      include_in_menu: 1
```

Multiple website supported
--------------------------

If you want you can create a client for different websites:

```php
$secondaryWebsite = Mage::app()->getWebsite('otherwebsite');
$client = self::createClient($secondaryWebsite);
```

This will use the secondary website base URL and sets the `MAGE_RUN_TYPE` and `MAGE_RUN_CODE` environment variables.

Test debug with xDebug
----------------------

Sometimes you need to debug your application during functional test execution. To do so you create an xDebug enabled client by passing `true` to the `createClient()` method:

```php
$client = self::createClient(true);
```
Don't forget to configure the `XDEBUG_SESSION` cookie value in your `app/etc/local.xml` file according your IDE settings. For example for PHPStorm on MAC this value should be `PHPSTORM`:

```xml
<?xml version="1.0"?>
<config>
	<!-- ... -->
	<phpunit>
        <functional>
            <xdebug_session>PHPSTORM</xdebug_session>
        </functional>
    </phpunit>
</config>
```

Open response in browser
------------------------
Sometimes is useful to open last response returned by Magento in your browser for further analysis. To do so you can use the `openResponseInBrowser()` method, for example:

```php
$client = self::createClient();
$client->request('GET', '/');
self::openResponseInBrowser($client)
```
This will dump the last response's content in a temporary file and opens it with a system command. Don't forget to configure the open file in browser command for your system in the `app/etc/local.xml` (the `%s` placeholder will be replaced with the temporary file path). For example on MAC you can use `open %s`:

```xml
<?xml version="1.0"?>
<config>
	<!-- ... -->
	<phpunit>
        <functional>
            <open_browser_command>open %s</open_browser_command>
        </functional>
    </phpunit>
</config>
```

Different front controller
--------------------------

It's also possible to specify an alternative front controller instead of `index.php`. This is extremely useful when you have to serve static file through the front controller `get.php` provided by Magento:

```php
$staticFilesClient = self::createClient(null, false, 'test', 'get.php');
$staticFilesClient->request('GET', '/path/to/media/file.jpg');
```

License
-------

This library is under the MIT license. See the complete license in the LICENSE file.

Credits
-------

Developed by [Webgriffe®](http://www.webgriffe.com/). Please, report to us any bug or suggestion by GitHub issues.
