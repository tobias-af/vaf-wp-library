<?php

/** @noinspection PhpUnused */

namespace VAF\WP\Library;

abstract class Plugin
{
    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    private string $pluginFile;

    final private function __construct(string $pluginFile)
    {
        $this->pluginFile = $pluginFile;

        $this->initPlugin();

        $this->startTrait('pluginCommunication');
        $this->startTrait('modules');
        $this->startTrait('shortcodes');
        $this->startTrait('restAPI');
        $this->startTrait('templatesPHP');
        $this->startTrait('adminPages');
    }

    final public function getPluginFile(): string
    {
        return $this->pluginFile;
    }

    final public function getPluginDirectory(): string
    {
        return plugin_dir_path($this->getPluginFile());
    }

    final private function startTrait(string $trait): void
    {
        $startMethod = 'start' . ucfirst($trait);
        if (method_exists($this, $startMethod)) {
            $this->$startMethod();
        }
    }

    /**
     * Starts the plugin and creates a new instance
     *
     * @param string $pluginFile
     * @return void
     */
    public static function start(string $pluginFile): void
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($pluginFile);
        }
    }

    /**
     * Function is called when the plugin starts and is initialized
     */
    abstract protected function initPlugin(): void;
}
