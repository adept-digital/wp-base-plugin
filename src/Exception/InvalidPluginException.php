<?php

namespace AdeptDigital\WpBasePlugin\Exception;

use RuntimeException;

/**
 * Invalid Plugin Exception
 *
 * Thrown when a plugin is invalid, inactive or missing.
 */
class InvalidPluginException extends RuntimeException
{
    /**
     * Invalid plugin file
     *
     * @var string
     */
    private $pluginFile;

    /**
     * Invalid Plugin Exception constructor.
     *
     * @param string $pluginFile
     */
    public function __construct(string $pluginFile)
    {
        parent::__construct("Invalid, inactive, or missing plugin: {$pluginFile}");
        $this->pluginFile = $pluginFile;
    }

    /**
     * Get the invalid plugin file.
     *
     * @return string
     */
    public function getPluginFile(): string
    {
        return $this->pluginFile;
    }
}