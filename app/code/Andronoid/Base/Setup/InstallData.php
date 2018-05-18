<?php

namespace Andronoid\Base\Setup;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\Group;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Theme\Model\Config;
use Magento\Theme\Model\Data\Design\Config as DesignConfig;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Config\Model\ResourceModel\Config as CoreConfigData;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;


/**
 * Andronoid\Base\Setup\InstallData
 *
 * @category    Andronoid
 * @package     Andronoid_Base
 */

class InstallData implements InstallDataInterface
{
    /**
     * website code
     */
    const WEBSITE_CODE = 'andronoid';

    /**
     * website name
     */
    const WEBSITE_NAME = 'Andronoid Website';

    /**
     * theme full path
     */
    const THEME_FULL_PATH = 'frontend/Andronoid/theme1';

    /**
     * theme code
     */
    const THEME_CODE = 'Andronoid/theme1';

    /**
     * root category name
     */
    const CATEGORY_NAME = 'Andronoid Root Category';

    /**
     * store group name
     */
    const STORE_GROUP_NAME = 'Andronoid Group';

    /**
     * store group code
     */
    const STORE_GROUP_CODE = 'andronoid_group';

    /**
     * store view code
     */
    const STORE_VIEW_CODE = 'andronoid_store';

    /**
     * store view name
     */
    const STORE_VIEW_NAME = 'Andronoid Store View';

    /**
     * Magento store website factory
     *
     * @var WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * Magento store group collection
     *
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * Magento store group factory
     *
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * Magento store factory
     *
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * Magento catalog category collection factory
     *
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Magento catalog category repository
     *
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Magento event manager
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CoreConfigData
     */
    protected $coreConfigData;

    /**
     * @var ListInterface
     */
    protected $themeList;

    /**
     * Magento framework indexer registry
     *
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * Magento framework reinitable config
     *
     * @var ReinitableConfigInterface
     */
    protected $reinitableConfig;

    /**
     * Magento theme collection factory
     *
     * @var ThemeCollectionFactory
     */
    protected $themeCollectionFactory;

    /**
     * InstallData constructor.
     *
     * @param WebsiteFactory $websiteFactory
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param GroupFactory $groupFactory
     * @param StoreFactory $storeFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryRepository $categoryRepository
     * @param ListInterface $themeList
     * @param ManagerInterface $eventManager
     * @param Config $config
     * @param IndexerRegistry $indexerRegistry
     * @param ReinitableConfigInterface $reinitableConfig
     * @param ThemeCollectionFactory $themeCollectionFactory

     */

    public function __construct(
        WebsiteFactory $websiteFactory,
        GroupCollectionFactory $groupCollectionFactory,
        GroupFactory $groupFactory,
        StoreFactory $storeFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryRepository $categoryRepository,
        ListInterface $themeList,
        ManagerInterface $eventManager,
        Config $config,
        IndexerRegistry $indexerRegistry,
        ReinitableConfigInterface $reinitableConfig,
        CoreConfigData $coreConfigData,
        ThemeCollectionFactory $themeCollectionFactory
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->themeList = $themeList;
        $this->eventManager = $eventManager;
        $this->config = $config;
        $this->indexerRegistry = $indexerRegistry;
        $this->reinitableConfig = $reinitableConfig;
        $this->coreConfigData = $coreConfigData;
        $this->themeCollectionFactory = $themeCollectionFactory;
    }


    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $website = $this->createWebsite();

        $category = $this->createRootCategory();

        $group = $this->createStoreGroup($website, $category);

        $store = $this->createStoreView($website, $group);

        if ($group->getDefaultStoreId() != $store->getId()) {
            $group->setDefaultStoreId($store->getId());
            $group->getResource()->save($group);
        }

        $this->assignTheme($website);

        $this->setDefaultTheme($website);

        $this->clean();

        $setup->endSetup();
    }
    /**
     * Create website or get the existing one
     *
     * @return Website
     */
    protected function createWebsite()
    {
        $website = $this->websiteFactory->create();
        $website->load(static::WEBSITE_CODE);

        if ($website->getId()) {
            // website already exists
            return $website;
        }

        $website->setCode(static::WEBSITE_CODE);
        $website->setName(static::WEBSITE_NAME);
        $website->setIsDefault(1);
        $website->getResource()->save($website);

        return $website;
    }

    /**
     * Create root category or get the existing one
     *
     * @return Category
     */
    protected function createRootCategory()
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addFieldToFilter('name', static::CATEGORY_NAME);
        $categoryCollection->addFieldToFilter('parent_id', Category::TREE_ROOT_ID);
        $categoryCollection->getSelect()->limit(1);

        /** @var Category $category */
        $category = $categoryCollection->getFirstItem();
        if ($category->getId()) {
            // category already exist
            return $category;
        }

        $category->setIsActive(1);
        $category->setParentId(Category::TREE_ROOT_ID);
        $category->setName(static::CATEGORY_NAME);
        $category->setIncludeInMenu(0);

        $trueRootCategory = $this->categoryRepository->get(Category::TREE_ROOT_ID);
        $category->setPath($trueRootCategory->getPath());

        $category->getResource()->save($category);

        return $category;
    }

    /**
     * Create store group or get the existing one
     *
     * @param Website  $website
     * @param Category $category
     *
     * @return Group
     */
    protected function createStoreGroup(Website $website, Category $category)
    {
        $groupCollection = $this->groupCollectionFactory->create();
        $groupCollection->addFieldToFilter('name', static::STORE_GROUP_NAME);

        /** @var Group $group */
        $group = $groupCollection->getFirstItem();
        if ($group && $group->getId()) {
            // store group already exist
            return $group;
        }

        $group->setName(static::STORE_GROUP_NAME);
        $group->setCode(static::STORE_GROUP_CODE);
        $group->setRootCategoryId($category->getId());
        $group->setWebsite($website);
        $group->getResource()->save($group);

        return $group;
    }

    /**
     * Create store view or get the existing one
     *
     * @param Website $website
     * @param Group   $group
     *
     * @return Store
     */
    protected function createStoreView(Website $website, Group $group)
    {
        /** @var Store $store */
        $store = $this->storeFactory->create();
        $store->load(static::STORE_VIEW_CODE);
        if ($store->getId()) {
            // store view already exists
            return $store;
        }

        $store->setCode(static::STORE_VIEW_CODE);
        $store->setName(static::STORE_VIEW_NAME);
        $store->setWebsite($website);
        $store->setGroupId($group->getId());
        $store->setIsActive(1);
        $store->getResource()->save($store);
        $this->eventManager->dispatch('store_add', ['store' => $store]);

        return $store;
    }

    /**
     * Assign Theme
     *
     * @param Website $website
     *
     * @return void
     */
    protected function assignTheme(Website $website)
    {
        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = $this->themeList->getThemeByFullPath(static::THEME_FULL_PATH);

        if (!$theme || !$theme->getCode()) {
            return;
        }
        $this->config->assignToStore($theme, [$website->getId()], ScopeInterface::SCOPE_WEBSITES);
    }

    /**
     * Set default theme
     *
     * @param Website $website
     *
     * @return void
     */
    protected function setDefaultTheme(Website $website)
    {
        $themeCollection = $this->themeCollectionFactory->create();
        $themeCollection->addAreaFilter(Area::AREA_FRONTEND);
        $themeCollection->addFieldToFilter('code', static::THEME_CODE);

        /** @var ThemeInterface $theme */
        $theme = $themeCollection->getFirstItem();

        if (!$theme || ($theme->getCode() != static::THEME_CODE)) {
            // theme is not installed
            return;
        }

        $this->coreConfigData->saveConfig(
            'design/theme/theme_id',
            $theme->getId(),
            ScopeInterface::SCOPE_WEBSITES,
            $website->getId()
        );
    }

    /**
     * clean the cache and reindex design grid
     */
    protected function clean()
    {
        $this->reinitableConfig->reinit();
        $this->indexerRegistry->get(DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();
    }
}

?>