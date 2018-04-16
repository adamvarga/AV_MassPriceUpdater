<?php

namespace AV\MassPriceUpdater\Model\Updater;

class MassUpdater
{
    protected $scopeConfig;
    protected $categoryFactory;
    protected $productCollectionFactory;
    protected $action;
    protected $storeManager;
    protected $appState;
    protected $indexerFactory;
    protected $indexerCollectionFactory;
    protected $cacheTypeList;
    protected $cacheFrontendPool;

    /**
     *   Define the default scope
     */

    const STORESCOPE = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    /**
     *   Get setup paths
     */

    const CATEGORIES = 'masspriceupdater_setup/general/category_list';
    const PRICE_TYPE = 'masspriceupdater_setup/general/price_attribute';
    const UPDATE_TYPE = 'masspriceupdater_setup/general/updater_type';
    const PRICE_AMOUNT = 'masspriceupdater_setup/general/price';
    const PERCENTAGE = 'masspriceupdater_setup/general/percentage';

    /**
     *   Define the default interfaces and set as variables
     */

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->categoryFactory = $categoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->action = $action;
        $this->storeManager = $storeManager;
        $this->appState = $appState;
        $this->indexerFactory = $indexerFactory;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->cacheTypeList = $cacheTypeList;
        $this->process();
    }

    /**
     *  Start the mass updater process
     * @return function
     */

    public function process()
    {
        return $this->getCategory();
    }

    /**
     *  Get store identifier
     * @return int
     */

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     *   Get category / categories id(s) from config
     * @return array
     */

    public function getCategory()
    {
        $categories = $this->scopeConfig->getValue(self::CATEGORIES, self::STORESCOPE);
        $categoryIds = explode(",", $categories);
        return $this->getCategoryProduct($categoryIds);
    }

    /**
     *  Get price type from config
     * @return array
     */

    public function getPriceType()
    {
        $types = $this->scopeConfig->getValue(self::PRICE_TYPE, self::STORESCOPE);
        $attributeCode = explode(",", $types);
        return $attributeCode;
    }

    /**
     *  Get update type from config
     * @return string
     */

    public function getUpdateType()
    {
        $updateType = $this->scopeConfig->getValue(self::UPDATE_TYPE, self::STORESCOPE);
        return $updateType;
    }

    /**
     *  Get price amount from config
     * @return float
     */

    public function getPriceAmount()
    {
        $priceAmount = $this->scopeConfig->getValue(self::PRICE_AMOUNT, self::STORESCOPE);
        if ($priceAmount) {
            return (float)$priceAmount;
        }
    }

    /**
     *  Get percentage value from config and set decimal value
     * @return float
     */

    public function getPercent()
    {
        $percent = $this->scopeConfig->getValue(self::PERCENTAGE, self::STORESCOPE);
        if (!$percent || !preg_match('/\\d/', $percent)) {
            return false;
        } else {
            $formatPercent = preg_replace('/[^0-9-]/', '', $percent);
            $decPercent = $formatPercent / 100;
            return $decPercent;
        }
    }

    /**
     *  Get product collections by category ids
     * @return array
     */

    public function getCategoryProduct($categoryIds)
    {
        $collection = $this->productCollectionFactory->create()
            ->addFieldToSelect($this->getPriceType())
            ->addFieldToSelect('*')
            ->addCategoriesFilter(array('in' => $categoryIds))
            ->load();

        return $this->getProducts($collection);
    }

    /**
     *  Reindex all
     */

    public function reindexAll()
    {
        $indexer = $this->indexerFactory->create();
        $indexerCollection = $this->indexerCollectionFactory->create();

        $ids = $indexerCollection->getAllIds();
        foreach ($ids as $id) {
            $idx = $indexer->load($id);
            if ($idx->getStatus() != 'valid') {
                $idx->reindexRow($id);
            }
        }
    }

    /**
     *  Flush magento caches
     */

    public function flushCache()
    {
        $types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    /**
     *  Get prices from collection and update these prices
     */

    public function getProducts($collection)
    {
        $updaterType = $this->getUpdateType();
        $priceType = $this->getPriceType();

        foreach ($collection as $productData) {

            $regularPrice = (float)$productData->getData('price');
            $specialPrice = (float)$productData->getData('special_price');

            foreach ($priceType as $type) {

                if ($type == 'price' && $regularPrice) {
                    if ($updaterType == 'fixed') {
                        $newFixRegularPrice = $regularPrice + $this->getPriceAmount();
                        $this->action->updateAttributes([$productData->getId()], ['price' => $newFixRegularPrice], $this->getStoreId());
                    } else {
                        $newPercentRegularPrice = $regularPrice + ($regularPrice * $this->getPercent());
                        $this->action->updateAttributes([$productData->getId()], ['price' => $newPercentRegularPrice], $this->getStoreId());
                    }
                }
                if ($type == 'special_price' && $specialPrice) {
                    if ($updaterType == 'fixed') {
                        $newFixSpecialPrice = $specialPrice + $this->getPriceAmount();
                        $this->action->updateAttributes([$productData->getId()], ['special_price' => $newFixSpecialPrice], $this->getStoreId());
                    } else {
                        $newPercentSpecialPrice = $specialPrice + ($specialPrice * $this->getPercent());
                        $this->action->updateAttributes([$productData->getId()], ['special_price' => $newPercentSpecialPrice], $this->getStoreId());
                    }
                }
            }
        }
        $this->reindexAll();
        $this->flushCache();
    }
}