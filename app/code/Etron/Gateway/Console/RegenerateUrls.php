<?php

namespace Etron\Gateway\Console;

use Magento\Framework\Exception\AlreadyExistsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State as AppState;

/**
 * Class RegenerateUrls.php
 */
class RegenerateUrls extends Command {
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
	 */
	protected $urlRewriteFactory;

	/**
	 * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
	 */
	protected $categoryCollectionFactory;

	protected $productCollectionFactory;

	protected $productUrlRewriteGenerator;

	protected $categoryUrlRewriteGenerator;

	protected $categoryRepository;

	protected $appState;

	/**
	 * RegenerateUrls constructor.
	 *
	 * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
	 * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
	 * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
	 * @param \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator
	 * @param string $name
	 */
	public function __construct(
		AppState\Proxy $appState,
		\Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator,
		\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
		$name = 'regenerate_urls'
	) {
		$this->appState                    = $appState;
		$this->urlRewriteFactory           = $urlRewriteFactory;
		$this->storeManager                = $storeManager;
		$this->categoryCollectionFactory   = $categoryCollectionFactory;
		$this->productCollectionFactory    = $productCollectionFactory;
		$this->productUrlRewriteGenerator  = $productUrlRewriteGenerator;
		$this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
		$this->categoryRepository          = $categoryRepository;
		parent::__construct( $name );
	}

	/**
	 * Configure the command
	 */
	protected function configure() {
		$this->setName( 'etron:regenerate_urls' );
		$this->setDescription( 'Regenerate Url\'s for categories and products' );
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		echo "Regenerating category urls";

		// set area code if needed
		try {
			$areaCode = $this->appState->getAreaCode();
		} catch ( \Magento\Framework\Exception\LocalizedException $e ) {
			// if area code is not set then magento generate exception "LocalizedException"
			$this->appState->setAreaCode( 'adminhtml' );
		}

		// save root category ids
		$rootCats = array();
		foreach ( $this->storeManager->getGroups() as $group ) {
			$rootCats[] = $group->getRootCategoryId();
		}

		/** @var \Magento\Store\Api\Data\StoreInterface $store */
		foreach ( $this->storeManager->getStores() as $store ) {
			$store_id = $store->getId();
			echo "\n" . $store->getCode() . ':';
			// Fetch all categories:
			$collection = $this->categoryCollectionFactory->create();

			$collection->addAttributeToSelect( '*' )->setStore( $store );
			$list = $collection->load();

			/** @var \Magento\Catalog\Model\Category $category */
			foreach ( $list as $category ) {
				if ( in_array( $category->getId(), $rootCats ) ) {
					continue;
				} // skip root categories as they don't have a valid request path

				$category->setStoreId( $store_id );
				$rewrite_data = $this->categoryUrlRewriteGenerator->generate( $category );
				if ( is_array( $rewrite_data ) && count( $rewrite_data ) ) {
					foreach ( $rewrite_data as $data ) {
						$urlRewrite = $this->urlRewriteFactory->create();
						$arrData    = $data->toArray();
						// fix trailing slash
						if ( substr( $arrData['request_path'], 0, 1 ) == '/' ) {
							$arrData['request_path'] = substr( $arrData['request_path'], 1 );
						}
						$urlRewrite->addData(
							$arrData
						);
						try {
							$urlRewrite->getResource()->save( $urlRewrite );
							echo '.';
						} catch ( AlreadyExistsException $alreadyExistsException ) {
							echo '-';
						}
					}

				}
			}
			echo "\n";
		}

		echo "\nRegenerating product urls\n";

		foreach ( $this->storeManager->getStores() as $store ) {
			$store_id   = $store->getId();
			$collection = $this->productCollectionFactory->create();
			$collection->addStoreFilter( $store_id )->setStoreId( $store_id );

			$collection->addAttributeToSelect( [ 'entity_id', 'url_path', 'url_key' ] );
			$list = $collection->load();
			foreach ( $list as $product ) {
				$product->setStoreId( $store_id );
				$rewrite_data = $this->productUrlRewriteGenerator->generate( $product );
				if ( is_array( $rewrite_data ) && count( $rewrite_data ) ) {
					foreach ( $rewrite_data as $data ) {
						$urlRewrite = $this->urlRewriteFactory->create();
						$arrData    = $data->toArray();
						// fix trailing slash
						if ( substr( $arrData['request_path'], 0, 1 ) == '/' ) {
							$arrData['request_path'] = substr( $arrData['request_path'], 1 );
						}
						$urlRewrite->addData(
							$arrData
						);
						try {
							$urlRewrite->getResource()->save( $urlRewrite );
							echo '.';
						} catch ( AlreadyExistsException $alreadyExistsException ) {
							echo '-';
						}
					}
				}
			}
		}
	}
}
