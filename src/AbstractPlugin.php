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
    private array $metaData;

    /**
     * Plugin constructor
     *
     * @param string $namespace
     * @param string $file
     */
    public function __construct(string $namespace, string $file)
    {
        parent::__construct($namespace, $file);

        if (!$this->isActive()) {
            throw new InvalidPluginException($file);
        }
    }

    /**
     * Check if the plugin is active.
     *
     * @return bool
     */
    private function isActive(): bool
    {
        // Is must use plugin?
        $pluginPath = wp_normalize_path(dirname($this->getFile()));
        $wpmuPluginPath = wp_normalize_path(WPMU_PLUGIN_DIR);
        if (str_starts_with($pluginPath, $wpmuPluginPath)) {
            return true;
        }

        // Is symlinked must use plugin?
        $pluginRealPath = wp_normalize_path(dirname(realpath($this->getFile())));
        if (in_array($pluginRealPath, $GLOBALS['wp_plugin_paths'])) {
            return true;
        }

        // Is active plugin?
        $plugin = $this->getId();
        $activePlugins = (array)get_option('active_plugins', []);
        if (in_array($plugin, $activePlugins, true)) {
            return true;
        }

        if (!is_multisite()) {
            return false;
        }

        // Is active network plugin?
        $activeNetworkPlugins = (array)get_site_option('active_sitewide_plugins', []);
        if (isset($activeNetworkPlugins[$plugin])) {
            return true;
        }

        return false;
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