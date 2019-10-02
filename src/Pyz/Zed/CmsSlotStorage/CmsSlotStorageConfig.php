<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\CmsSlotStorage;

use Pyz\Zed\Synchronization\SynchronizationConfig;
use Spryker\Zed\CmsSlotStorage\CmsSlotStorageConfig as SprykerCmsSlotStorageConfig;

class CmsSlotStorageConfig extends SprykerCmsSlotStorageConfig
{
    /**
     * @return string|null
     */
    public function getCmsStorageSynchronizationPoolName(): ?string
    {
        return SynchronizationConfig::DEFAULT_SYNCHRONIZATION_POOL_NAME;
    }
}
