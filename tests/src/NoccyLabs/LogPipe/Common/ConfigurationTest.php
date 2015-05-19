<?php

namespace NoccyLabs\LogPipe\Common;

class ConfigurationTest extends \PhpUnit_Framework_TestCase
{
    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function testThatTheConfigurationWorksWithDefaults()
    {
        $config = new Configuration();
        $this->assertNotNull($config);
    }

    public function testThatPipesAreTheDefaultType()
    {
        $config = new Configuration("hello");
        $this->assertEquals("pipe", $config->getType());
        $this->assertEquals("hello", $config->getResource());
    }

    public function testThatUrisCanBeParsed()
    {
        $config = new Configuration("foo:bar?baz=true&bin=yes");
        $this->assertEquals("foo", $config->getType());
        $this->assertEquals("bar", $config->getResource());
        $this->assertEquals("true", $config->getOption("baz"));
        $this->assertEquals("yes", $config->getOption("bin"));

        $all = $config->getAllOptions();
        $this->assertEquals(2, count($all));
        $this->assertArrayHasKey("baz", $all);
        $this->assertEquals("true", $all["baz"]);
        $this->assertArrayHasKey("bin", $all);
        $this->assertEquals("yes", $all["bin"]);
    }

}
