<?php

namespace AdeptDigital\WpBasePlugin;

use AdeptDigital\WpBaseComponent\AbstractComponent;
use AdeptDigital\WpBasePlugin\Exception\InvalidPluginException;

/**
 * Base Plugin
 */
abstract class AbstractPlugin extends AbstractComponent implements PluginInterface
{
    /**
     * Meta data cache
     *
     * @var array
     */
    private $metaData;

    /**
     * Plugin constructor
     *
     * @param string $namespace
     * @param string $file
     */
    public function __construct(string $namespace, string $file)
    {
        parent::__construct($namespace, $file);
    }

    /**
     * @inheritDoc
     */
    public function __invoke(): void
    {
        parent::__invoke();
        add_action('plugins_loaded', [$this, 'doBoot']);
    }

    /**
     * Triggers namespaced boot action.
     *
     * Passes `$this` as the first parameter to allow hooking into the
     * plugin.
     *
     * @return void
     */
    public function doBoot(): void
    {
        do_action($this->getNamespace('boot'), $this);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return plugin_basename($this->getFile());
    }

    /**
     * @inheritDoc
     */
    public function getBasePath(): string
    {
        return untrailingslashit(plugin_dir_path($this->getFile()));
    }

    /**
     * @inheritDoc
     */
    public function getBaseUri(): string
    {
        return untrailingslashit(plugin_dir_url($this->getFile()));
    }

    /**
     * @inheritDoc
     */
    public function getMetaData(string $name): ?string
    {
        if (!isset($this->metaData)) {
            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if (!file_exists($this->getFile())) {
                throw new InvalidPluginException($this->getFile());
            }

            $this->metaData = get_plugin_data($this->getFile(), false, true);
        }

        if (!isset($this->metaData[$name]) || $this->metaData[$name] === '') {
            return null;
        }

        return $this->metaData[$name];
    }
}