<?php

namespace Akeneo\Component\Versioning;

use Akeneo\Component\Versioning\Model\VersionableInterface;
use Pim\Bundle\VersioningBundle\Model\Version;

/**
 * Builds versions for a bulk of versionable objects.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface BulkVersionBuilderInterface
{
    /**
     * Build versions from the specified versionables
     *
     * @param VersionableInterface[] $versionables
     *
     * @return Version[]
     */
    public function buildVersions(array $versionables);
}
