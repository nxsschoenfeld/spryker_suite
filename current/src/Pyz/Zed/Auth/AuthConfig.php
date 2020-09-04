<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\Auth;

use Spryker\Zed\Auth\AuthConfig as SprykerAuthConfig;

class AuthConfig extends SprykerAuthConfig
{
    /**
     * @return array
     */
    public function getIgnorable()
    {
        $this->addIgnorable('health-check', 'index', 'index');
        $this->addIgnorable('_profiler', 'wdt', '*');
        $this->addIgnorable('authentication-merchant-portal-gui', 'login', 'index');

        return parent::getIgnorable();
    }
}
