<?php

/**
 * @file
 * Contains DavidBarratt\CustomInstaller\ConfigurationAlpha1.
 */

namespace DavidBarratt\CustomInstaller;

/**
 * Wraps the legacy plugin configuration.
 *
 * @deprecated
 */
class ConfigurationAlpha1 extends Configuration
{
    /**
     * {@inheritdoc}
     */
    protected function convert($extra)
    {
        $this->types = $extra['custom-installer'];
    }
}
