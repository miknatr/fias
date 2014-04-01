<?php

namespace Fias\Tests;

use Browser\TestBrowserTrait;
use Fias\ApiAction\Completion;
use Fias\Container;

class ApiControllerTest extends \PHPUnit_Framework_TestCase
{
    use TestBrowserTrait;

    /** @var Container */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
        $this->prepareHttpClient('http://' . $this->container->getHost());
    }

    public function testComplete()
    {
        $tooBigLimit = Completion::MAX_LIMIT + 1;
        $this->loadPage('/api/complete/?address=Москва&limit=' . $tooBigLimit, 400);
    }

    public function testValidate()
    {
        $this->loadPage('/api/validate/', 400);
    }

    public function testNotFound()
    {
        $this->loadPage('/totally/wrong/destination', 404);
    }
}
