<?php

namespace DavidBarratt\CustomInstaller;

use Composer\Package\PackageInterface;

/**
 * Wrapper for plugin configuration.
 */
class Configuration
{

    /**
     * @var array
     */
    protected $types = array();

    /**
     * @param array $extra
     */
    public function __construct($extra = array())
    {
        $this->convert($extra);
    }

    /**
     * Retrieve the pattern for the given package.
     *
     * @param \Composer\Package\PackageInterface $package
     *
     * @return string
     */
    public function getPattern(PackageInterface $package)
    {
        return $this->types[$package->getType()];
    }

    /**
     * Converts the given extra data to relevant configuration values.
     */
    protected function convert($extra)
    {
        $this->types = $extra['custom-installer'];
    }
}