<?php

namespace AdeptDigital\WpBasePlugin\Tests\Unit;

use AdeptDigital\WpBasePlugin\AbstractPlugin;
use AdeptDigital\WpBasePlugin\Exception\InvalidPluginException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractPluginTest extends TestCase
{
    private function createMockPlugin(string $file = TEST_DATA_DIR . '/test-plugin/test-plugin.php'): AbstractPlugin
    {
        return $this->getMockBuilder(AbstractPlugin::class)
            ->setConstructorArgs(['abc', $file])
            ->getMockForAbstractClass();
    }

    private function createMockCallable(): MockObject
    {
        return $this
            ->getMockBuilder(\stdclass::class)
            ->addMethods(['__invoke'])
            ->getMock();
    }

    public function testConstructMissingFile()
    {
        $this->expectException(InvalidPluginException::class);
        $this->createMockPlugin('/not-found.php');
    }

    public function testInvoke()
    {
        $plugin = $this->createMockPlugin();
        $plugin();
        $this->assertIsInt(has_action('plugins_loaded', [$plugin, 'doBoot']));
        $this->assertIsInt(has_action('init', [$plugin, 'doInit']));
    }

    public function testDoBoot()
    {
        $plugin = $this->createMockPlugin();
        $callable = $this->createMockCallable();
        $callable->expects(self::once())
            ->method('__invoke')
            ->with(self::identicalTo($plugin));

        add_action('abc_boot', $callable);
        $plugin->doBoot();
    }

    public function testGetId()
    {
        $plugin = $this->createMockPlugin();
        $this->assertEquals('test-plugin/test-plugin.php', $plugin->getId());
    }

    public function testGetBasePath()
    {
        $plugin = $this->createMockPlugin();
        $this->assertEquals(TEST_DATA_DIR . '/test-plugin', $plugin->getBasePath());
    }

    public function testGetBaseUri()
    {
        $plugin = $this->createMockPlugin();
        $this->assertEquals(WP_PLUGIN_URL . '/test-plugin', $plugin->getBaseUri());
    }

    public function testGetMetaData()
    {
        $plugin = $this->createMockPlugin();
        $this->assertEquals(
            'My Test Plugin',
            $plugin->getMetaData('Name')
        );
        $this->assertEquals(
            'https://adeptdigital.com.au/wordpress/plugins/my-test-plugin/',
            $plugin->getMetaData('PluginURI')
        );
        $this->assertNull($plugin->getMetaData('Description'));
        $this->assertNull($plugin->getMetaData('NotAField'));
    }
}
