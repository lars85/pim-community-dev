<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model\Projection;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Model\Completeness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollectionFactory;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class ProductCompletenessCollectionFactorySpec extends ObjectBehavior
{
    function it_is_a_product_completeness_collection_factory()
    {
        $this->shouldHaveType(ProductCompletenessCollectionFactory::class);
    }

    function it_builds_a_product_completeness_collection()
    {
        $locale = new Locale();
        $locale->setCode('en_US');
        $channel = new Channel();
        $channel->setCode('ecommerce');
        $product = new Product();
        $product->setId(42);
        $missingAttrribute = new Attribute();
        $missingAttrribute->setCode('name');

        $completeness = new Completeness($product, $channel, $locale, new ArrayCollection([$missingAttrribute]), 1, 5);
        $product->setCompletenesses(new ArrayCollection([$completeness]));

        $collection = $this->fromProduct($product);
        $collection->shouldBeAnInstanceOf(ProductCompletenessCollection::class);
        $collection->productId()->shouldReturn(42);

        $productCompletenesses = $collection->getIterator()->getArrayCopy();
        $productCompletenesses->shouldBeLike([
            'ecommerce-en_US' => new ProductCompleteness('ecommerce', 'en_US', 5, ['name'])
        ]);
    }

    function it_throws_an_error_if_product_has_no_id()
    {
        $this->shouldThrow(\TypeError::class)->during('fromProduct', [new Product()]);
    }
}
