<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\Console;

use Pyz\Shared\Console\ConsoleConstants;
use Spryker\Zed\Console\ConsoleConfig as SprykerConsoleConfig;

class ConsoleConfig extends SprykerConsoleConfig
{
    /**
     * @return bool
     */
    public function isDevelopmentConsoleCommandsEnabled(): bool
    {
        return $this->get(ConsoleConstants::ENABLE_DEVELOPMENT_CONSOLE_COMMANDS, $this->getDevelopmentConsoleCommandsDefaultValue());
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return bool
     */
    protected function getDevelopmentConsoleCommandsDefaultValue(): bool
    {
        return APPLICATION_ENV === 'development' || APPLICATION_ENV === 'devtest';
    }
}
