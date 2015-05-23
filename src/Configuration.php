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
     * @var array
     */
    protected $packages = array();

    /**
     * @var bool
     */
    protected $allPackageTypes = false;

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
        if (isset($this->packages[$package->getName()])) {
            return $this->packages[$package->getName()];
        } elseif (isset($this->packages[$package->getPrettyName()])) {
            return $this->packages[$package->getPrettyName()];
        } elseif (isset($this->types[$package->getType()])) {
            return $this->types[$package->getType()];
        }
    }

    /**
     * Checks if the given configuration will handle the package type.
     *
     * @param string $package_type
     *
     * @return bool
     */
    public function isPackageTypeSupported($packageType)
    {
        if ($this->allPackageTypes) {
            return true;
        } else {
            return isset($this->types[$packageType]);
        }
    }

    /**
     * Converts the given extra data to relevant configuration values.
     */
    protected function convert($extra)
    {
        // If the custom installer shall handle all package types, the setting
        // needs to be set in the extra config.
        if (!empty($extra['custom-installer-all-package-types'])) {
            $this->allPackageTypes = true;
        }

        // Backwards compatibility.
        // @todo: separate interface?
        if ($this->isAlpha1($extra)) {
            return $this->convertDataAlpha1($extra);
        }

        // New config format.
        if (isset($extra['custom-installer'])) {
            foreach ($extra['custom-installer'] as $pattern => $specs) {
                foreach ($specs as $spec) {
                    $match = array();
                    // Type matching
                    if (preg_match('/^type:(.*)$/', $spec, $match)) {
                        $this->types[$match[1]] = $pattern;
                    } // Else it must be the package name.
                    else {
                        $this->packages[$spec] = $pattern;
                    }
                }
            }
        }
    }

    /**
     * Automatically checks for the version of the configuration array.
     *
     * @param $data
     */
    protected function isAlpha1($extra)
    {

        if (!isset($extra['custom-installer'])) {
            return false;
        }

        foreach ($extra['custom-installer'] as $key => $val) {
            if (is_array($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converts extra configuration in usable configuration information.
     *
     * @param array $extra Composerjson extra configuration
     */
    protected function convertDataAlpha1($extra)
    {
        $this->types = $extra['custom-installer'];
    }
}
