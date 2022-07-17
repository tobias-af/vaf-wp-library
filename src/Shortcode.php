<?php

namespace VAF\WP\Library;

abstract class Shortcode
{
    private Plugin $plugin;

    /**
     * Setter for $plugin
     *
     * @param Plugin $plugin
     * @return $this
     */
    final public function setPlugin(Plugin $plugin): Shortcode
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * Getter for $plugin
     *
     * @return Plugin
     */
    final public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    /**
     * Function should return the name of the shortcode
     *
     * @return string
     */
    abstract public function getShortcode(): string;

    /**
     * Handler function for the shortcode
     * All allowed attributes are available as array
     *
     * @param array $attributes
     * @param string|null $content
     * @return void
     */
    abstract public function handle(array $attributes, ?string $content = null): string;

    /**
     * Function should return an array of allowed attributes for the shortcode where
     * the array key is the name of the attribute and the value is the default if the attribute
     * is not given in the shortcode
     *
     * @return array
     */
    abstract protected function getAttributes(): array;

    /**
     * Callback to handle the shortcode
     *
     * @param array $attributes
     * @param string|null $content
     * @param string $tag
     * @return string
     */
    final public function callback(array $attributes, ?string $content, string $tag): string
    {
        $attributes = shortcode_atts($this->getAttributes(), $attributes, $tag);

        return $this->handle($attributes, $content);
    }
}
