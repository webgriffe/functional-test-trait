Webgriffe Functional Test Trait
===============================

Trait which enables functional testing in Magento with [EcomDev_PHPUnit](https://github.com/EcomDev/EcomDev_PHPUnit) and using a [CgiHttpKernel](https://github.com/igorw/CgiHttpKernel).

Installation
------------

Import it using Composer (note, it's a dev dependency):

    composer require --dev webgriffe/functional-test-trait
    
Usage
-----

The suggested usage of this trait is in combination with [EcomDev_PHPUnit](https://github.com/EcomDev/EcomDev_PHPUnit) and the [Webgriffe's Magento Config Extension](https://github.com/webgriffe/config-extension).
So, first of all, add these dependencies to your Magento project:

	$ composer require --dev webgriffe/functional-test-trait \
		ecomdev/ecomdev_phpunit
	$ composer require webgriffe/config-extension
	
Another suggested dependency is `symfony/css-selector`, which allows to select DOM elements using CSS selectors in your functional tests:

	$ composer require --dev symfony/css-selector

Then setup your test suite as usual with EcomDev_PHPUnit and in your `phpunit.xml.dist` add the following environment variable in the `<php>` XML node:

	<env name="MAGE_LOAD_ECOMDEV_PHPUNIT_CONFIG" value="1" />
	
This environment variable tells to the Webgriffe's Magento Config Extension to load the EcomDev\_PHPUnit base config file (located at `app/etc/local.phpunit.xml`). In this way, during your functional tests, the Magento application will use the same test database used by other EcomDev\_PHPUnit tests and you'll also be able to load fixtures.

Also add a `<testsuite>` node to your `phpunit.xml.dist` to group your functional tests in a dedicated test suite:

	<testsuite name="My Project Functional Test Suite">
        <directory suffix="Test.php">tests</directory>
    </testsuite>

Now you're ready to write your first Magento functional test. 
Put a test your `tests/` directory. For example, `HomepageTest.php`:

	<?php
	
	class HomepageTest extends EcomDev_PHPUnit_Test_Case
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
	
As you may notice there is the `@loadFixture` annotation which loads data from `category.yaml` file located at `tests/HomepageTest/fixtures/category.yaml`.

License
-------

This library is under the MIT license. See the complete license in the LICENSE file.

Credits
-------

Developed by [Webgriffe®](http://www.webgriffe.com/). Please, report to us any bug or suggestion by GitHub issues.