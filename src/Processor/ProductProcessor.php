<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductProcessor implements ResourceProcessorInterface
{
    /** @var ProductFactoryInterface */
    private $productFactory;

    /** @var RepositoryInterface */
    private $productRepository;

    /** @var ObjectManager */
    private $productManager;

    /** @var RepositoryInterface */
    private $taxonRepository;

    /** @var FactoryInterface */
    private $productTaxonFactory;

    /** @var RepositoryInterface */
    private $channelRepository;

    /** @var FactoryInterface */
    private $productImageFactory;

    /** @var FactoryInterface */
    private $productAssociationFactory;

    /** @var FactoryInterface */
    private $channelPricingFactory;

    /** @var FactoryInterface */
    private $productAttributeValueFactory;

    /** @var RepositoryInterface */
    private $productAttributeRepository;

    /** @var RepositoryInterface */
    private $productOptionRepository;

    /** @var FactoryInterface */
    private $productVariantFactory;

    /** @var RepositoryInterface */
    private $productVariantRepository;

    /** @var RepositoryInterface */
    private $taxCategoryRepository;

    /** @var RepositoryInterface */
    private $shippingCategoryRepository;

    /** @var RepositoryInterface */
    private $productOptionValueRepository;

    /** @var RepositoryInterface */
    private $productAssociationTypeRepository;

    /** @var MetadataValidatorInterface */
    private $metadataValidator;

    /** @var string[] */
    private $headerKeys;

    /**
     * @param string[] $headerKeys
     */
    public function __construct(
        ProductFactoryInterface $productFactory,
        RepositoryInterface $productRepository,
        ObjectManager $productManager,
        RepositoryInterface $taxonRepository,
        FactoryInterface $productTaxonFactory,
        RepositoryInterface $channelRepository,
        FactoryInterface $productImageFactory,
        FactoryInterface $productAssociationFactory,
        FactoryInterface $productAttributeValueFactory,
        FactoryInterface $channelPricingFactory,
        RepositoryInterface $productAttributeRepository,
        RepositoryInterface $productOptionRepository,
        FactoryInterface $productVariantFactory,
        RepositoryInterface $productVariantRepository,
        RepositoryInterface $taxCategoryRepository,
        RepositoryInterface $shippingCategoryRepository,
        RepositoryInterface $productOptionValueRepository,
        RepositoryInterface $productAssociationTypeRepository,
        MetadataValidatorInterface $metadataValidator,
        array $headerKeys
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->productManager = $productManager;
        $this->taxonRepository = $taxonRepository;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->channelRepository = $channelRepository;
        $this->productImageFactory = $productImageFactory;
        $this->productAssociationFactory = $productAssociationFactory;
        $this->productAttributeValueFactory = $productAttributeValueFactory;
        $this->channelPricingFactory = $channelPricingFactory;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->productVariantFactory = $productVariantFactory;
        $this->productVariantRepository = $productVariantRepository;
        $this->taxCategoryRepository = $taxCategoryRepository;
        $this->shippingCategoryRepository = $shippingCategoryRepository;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->productAssociationTypeRepository = $productAssociationTypeRepository;
        $this->metadataValidator = $metadataValidator;
        $this->headerKeys = $headerKeys;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        /** @var ProductInterface $product */
        $product = $this->getProduct($data['Code']);
        $product->setMainTaxon($this->taxonRepository->findOneBy(['code' => $data['MainTaxon']]));
        $product->setEnabled($data['Enabled']);
        $product->setVariantSelectionMethod($data['VariantSelectionMethod']);
        $product->setAverageRating($data['AverageRating']);

        foreach ($data['Translations'] as $locale => $translation) {
            $product->setCurrentLocale($locale);
            $product->setFallbackLocale($locale);

            $product->setName($translation['Name']);
            $product->setSlug($translation['Slug']);
            $product->setDescription($translation['Description']);
            $product->setShortDescription($translation['ShortDescription']);
            $product->setMetaKeywords($translation['MetaKeywords']);
            $product->setMetaDescription($translation['MetaDescription']);
        }

        foreach ($data['Taxons'] as $taxonData) {
            /** @var ProductTaxonInterface $productTaxon */
            $productTaxon = $this->productTaxonFactory->createNew();
            $productTaxon->setTaxon($this->taxonRepository->findOneBy(['code' => $taxonData['Taxon']]));
            $productTaxon->setPosition($taxonData['Position']);

            $product->addProductTaxon($productTaxon);
        }

        foreach ($data['Channels'] as $channelCode) {
            $product->addChannel($this->channelRepository->findOneBy(['code' => $channelCode]));
        }

        foreach ($data['Images'] as $imageData) {
            /** @var ProductImageInterface $productImage */
            $productImage = $this->productImageFactory->createNew();
            $productImage->setType($imageData['Type']);
            $productImage->setPath($imageData['Path']);

            $product->addImage($productImage);
        }

        foreach ($data['Associations'] as $associationData) {
            /** @var ProductAssociationInterface $productAssociation */
            $productAssociation = $this->productAssociationFactory->createNew();
            $productAssociation->setType($this->productAssociationTypeRepository->findOneBy(['code' => $associationData['Type']]));

            foreach ($associationData['Products'] as $productCode) {
                $productAssociation->addAssociatedProduct($productCode === $data['Code'] ? $product : $this->getProduct($productCode));
            }

            $product->addAssociation($productAssociation);
        }

        foreach ($data['Attributes'] as $attributeData) {
            /** @var ProductAttributeValueInterface $productAttributeValue */
            $productAttributeValue = $this->productAttributeValueFactory->createNew();
            $productAttributeValue->setAttribute($this->productAttributeRepository->findOneBy(['code' => $attributeData['Attribute']]));
            $productAttributeValue->setLocaleCode($attributeData['Locale']);
            $productAttributeValue->setValue($attributeData['Value']);

            $product->addAttribute($productAttributeValue);
        }

        foreach ($data['Options'] as $optionCode) {
            $product->addOption($this->productOptionRepository->findOneBy(['code' => $optionCode]));
        }

        foreach ($data['Variants'] as $variantData) {
            $productVariant = $this->getProductVariant($variantData['Code']);
            $productVariant->setPosition($variantData['Position']);
            $productVariant->setVersion($variantData['Version']);
            $productVariant->setOnHold($variantData['OnHold']);
            $productVariant->setOnHand($variantData['OnHand']);
            $productVariant->setTracked($variantData['Tracked']);
            $productVariant->setWidth($variantData['Width']);
            $productVariant->setHeight($variantData['Height']);
            $productVariant->setDepth($variantData['Depth']);
            $productVariant->setWeight($variantData['Weight']);
            $productVariant->setShippingRequired($variantData['ShippingRequired']);
            $productVariant->setTaxCategory($this->taxCategoryRepository->findOneBy(['code' => $variantData['TaxCategory']]));
            $productVariant->setShippingCategory($this->shippingCategoryRepository->findOneBy(['code' => $variantData['ShippingCategory']]));

            foreach ($variantData['Images'] as $imagePath) {
                $productVariant->addImage($product->getImages()->filter(function (ProductImageInterface $productImage) use ($imagePath): bool {
                    return $productImage->getPath() === $imagePath;
                })->first());
            }

            foreach ($variantData['Translations'] as $localeCode => $translation) {
                $productVariant->setFallbackLocale($localeCode);
                $productVariant->setCurrentLocale($localeCode);

                $productVariant->setName($translation['Name']);
            }

            foreach ($variantData['ChannelPricings'] as $channelCode => $channelPricingData) {
                /** @var ChannelPricingInterface $channelPricing */
                $channelPricing = $this->channelPricingFactory->createNew();
                $channelPricing->setChannelCode($channelCode);
                $channelPricing->setPrice($channelPricingData['Price']);
                $channelPricing->setOriginalPrice($channelPricingData['OriginalPrice']);

                $productVariant->addChannelPricing($channelPricing);
            }

            foreach ($variantData['Options'] as $optionValueCode) {
                $productVariant->addOptionValue($this->productOptionValueRepository->findOneBy(['code' => $optionValueCode]));
            }

            $product->addVariant($productVariant);
        }

        $this->productManager->flush();
    }

    private function getProduct(string $code): ProductInterface
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => $code]);

        if ($product === null) {
            /** @var ProductInterface $product */
            $product = $this->productFactory->createNew();
            $product->setCode($code);

            $this->productManager->persist($product);
        }

        return $product;
    }

    private function getProductVariant(string $code): ProductVariantInterface
    {
        /** @var ProductVariantInterface|null $productVariant */
        $productVariant = $this->productVariantRepository->findOneBy(['code' => $code]);

        if ($productVariant === null) {
            /** @var ProductVariantInterface $productVariant */
            $productVariant = $this->productVariantFactory->createNew();
            $productVariant->setCode($code);
        }

        return $productVariant;
    }
}
