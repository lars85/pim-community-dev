<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\AbstractProcessor;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\BusinessValidationException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Product import processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $arrayConverter;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter array converter
     * @param IdentifiableObjectRepositoryInterface $repository     product repository
     * @param ProductBuilderInterface               $productBuilder product builder
     * @param ProductUpdaterInterface               $productUpdater product updater
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ProductBuilderInterface $productBuilder,
        ProductUpdaterInterface $productUpdater
    ) {
        parent::__construct($repository);

        $this->productBuilder = $productBuilder;
        $this->arrayConverter = $arrayConverter;
        $this->productUpdater = $productUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $identifier    = $this->getIdentifier($convertedItem);
        $familyCode    = $this->getFamilyCode($convertedItem);
        $filteredItem  = $this->filterItemData($convertedItem);

        $product = $this->findOrCreateProduct($identifier, $familyCode);
        try {
            $this->productUpdater->update($product, $filteredItem);
        } catch (BusinessValidationException $exception) {
            $this->skipItemWithConstraintViolations($item, $exception->getViolations());
        }

        return $product;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->arrayConverter->convert($item);
    }

    /**
     * @param array $convertedItem
     *
     * @return string
     */
    protected function getIdentifier(array $convertedItem)
    {
        $identifierProperty = $this->repository->getIdentifierProperties();

        return $convertedItem[$identifierProperty[0]][0]['data'];
    }

    /**
     * @param array $convertedItem
     *
     * @return string|null
     */
    protected function getFamilyCode(array $convertedItem)
    {
        return $convertedItem['family'];
    }

    /**
     * Filters item data to remove associations which are imported through a dedicated processor because we need to
     * create any products before to associate them
     *
     * @param array $convertedItem
     *
     * @return array
     */
    protected function filterItemData(array $convertedItem)
    {
        unset($convertedItem[$this->repository->getIdentifierProperties()[0]]);
        unset($convertedItem['associations']);
        // TODO: until we split groups and variant group columns in the csv file
        unset($convertedItem['groups']);

        return $convertedItem;
    }

    /**
     * @param string $identifier
     * @param string $familyCode
     *
     * @return ProductInterface
     */
    protected function findOrCreateProduct($identifier, $familyCode)
    {
        $product = $this->repository->findOneByIdentifier($identifier);
        if (false === $product) {
            $product = $this->productBuilder->createProduct($identifier, $familyCode);
        }

        return $product;
    }
}
