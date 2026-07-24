<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Concerns;

use CmsOrbit\Lms\LmsServiceProvider;

/**
 * Maps an Entity's permission points to the `lms.entities.{uriKey}.*` namespace
 * registered by {@see LmsServiceProvider}.
 */
trait HasLmsPermissions
{
    public function permissionKey(): string
    {
        return 'lms.entities.'.static::uriKey();
    }
}
