<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Projection;

use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessCollectionFactory
{
    public function fromProduct(ProductInterface $product): ProductCompletenessCollection
    {
        $productCompletenesses = $product->getCompletenesses()->map(function(CompletenessInterface $completeness) {
            return new ProductCompleteness(
                $completeness->getChannel()->getCode(),
                $completeness->getLocale()->getCode(),
                $completeness->getRequiredCount(),
                $completeness->getMissingAttributes()->map(function(AttributeInterface $attribute) {
                    return $attribute->getCode();
                })->toArray()
            );
        })->toArray();

        return new ProductCompletenessCollection($product->getId(), $productCompletenesses);
    }
}
