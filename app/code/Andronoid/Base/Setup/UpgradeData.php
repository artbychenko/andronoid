<?php

namespace Andronoid\Base\Setup;

use Andronoid\Base\Setup\InstallData as AndronoidBaseInstallData;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\Data\PageInterface;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Andronoid\Base\Setup\UpgradeData
 *
 * @category    Andronoid
 * @package     Andronoid_Base
 */

class UpgradeData implements UpgradeDataInterface
{

    /**
     * Cms home page identifier
     */
    const CMS_PAGE_THEME1_HOME = 'theme1_home';

    /**
     * Magento Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $installer;

    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * @var PageInterfaceFactory
     */
    protected $pageFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepositoryInterface;

    /**
     * @var PageInterface
     */
    protected $pageInterface;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;


    /**
     * UpgradeData constructor.
     * @param StoreManagerInterface $storeManager
     * @param PageInterfaceFactory $pageFactory
     * @param Config $config
     * @param PageRepositoryInterface $pageRepositoryInterface
     * @param PageInterface $pageInterface
     * @param BlockFactory $blockFactory
     * @param EavSetupFactory $eavSetupFactory
     */

    public function __construct(
        StoreManagerInterface $storeManager,
        PageInterfaceFactory $pageFactory,
        Config $config,
        PageRepositoryInterface $pageRepositoryInterface,
        PageInterface $pageInterface,
        BlockFactory $blockFactory,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->storeManager = $storeManager;
        $this->pageFactory = $pageFactory;
        $this->config = $config;
        $this->pageRepositoryInterface = $pageRepositoryInterface;
        $this->pageInterface = $pageInterface;
        $this->blockFactory = $blockFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Upgrade data
     *
     * @param  ModuleDataSetupInterface $setup
     * @param  ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->installer = $setup;
        $this->context = $context;

        $this->upgradeToVersion('0.0.2');
        $this->upgradeToVersion('0.0.3');

    }

    /**
     * Runs upgrade to version number method
     *
     * @param $versionNumber
     */
    protected function upgradeToVersion($versionNumber)
    {
        if (version_compare($this->context->getVersion(), $versionNumber, '<')) {
            $methodName = 'upgradeTo_' . str_replace('.', '', $versionNumber);
            $this->$methodName();
        }
    }

    /**
     *
     * creates and configures home page
     */
    protected function upgradeTo_002()
    {
        $this->installer->startSetup();

        /** @var \Magento\Cms\Model\Page $cmsPage */
        $cmsPage = $this->pageFactory->create();
        $cmsPage->getResource()->load($cmsPage, static::CMS_PAGE_THEME1_HOME, 'identifier');

        if ($cmsPage->getId()) {
            // CMS page already exist
            return;
        }

        $store = $this->storeManager->getStore(AndronoidBaseInstallData::STORE_VIEW_CODE);
        $storeViewId = $store->getId();

        $cmsPage->setData([
            'stores' => [$storeViewId],
            'title' => 'Theme1 Home',
            'identifier' => static::CMS_PAGE_THEME1_HOME,
            'content' => '',
            'is_active' => Block::STATUS_ENABLED,
            'page_layout' => '1column',
        ]);

        $cmsPage->getResource()->save($cmsPage);

        $this->config->saveConfig(
            'web/default/cms_home_page',
            static::CMS_PAGE_THEME1_HOME,
            ScopeInterface::SCOPE_STORES,
            $storeViewId
        );

        $this->installer->endSetup();
    }

    /**
     *
     * adds content to home page
     */
    protected function upgradeTo_003()
    {
        $cmsPageContent = <<<HTML
        <section class="top-section">top section</section>
        <section class="main-section">main section</section>
        <section class="bottom-section">bottom section</section>
HTML;

        $this->installer->startSetup();

        /** @var \Magento\Cms\Model\Page $cmsPage */
        $cmsPage = $this->pageFactory->create();
        $cmsPage->getResource()->load($cmsPage, static::CMS_PAGE_THEME1_HOME, 'identifier');

        if (!$cmsPage->getId()) {
            // CMS page not exists
            return;
        }

        $cmsPage->setContent($cmsPageContent);
        $cmsPage->getResource()->save($cmsPage);

        $this->installer->endSetup();
    }

    /**
     *
     * adds/removes attribute 'manufacturer' to Images group
     */
    protected function addAttribute()
    {

        $this->installer->startSetup();

        /* @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->installer]);

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);

        $attributeId = $eavSetup->getAttributeId(Product::ENTITY, 'manufacturer');

        $groupId = (int)$eavSetup->getAttributeGroupByCode(
            Product::ENTITY,
            $attributeSetId,
            'image-management',
            'attribute_group_id'
        );

        if (empty($attributeId) && isset($attributeSetId) && isset($groupId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'manufacturer',
                [
                    'type' => 'varchar',
                    'label' => 'Manufacturer',
                    'input' => 'media_image',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
                    'required' => false,
                    'used_in_product_listing' => true,
                ]);

            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                $attributeSetId,
                $groupId,
                'manufacturer',
                5
            );
        }

        /*$eavSetup->removeAttribute(
            Product::ENTITY,
            'manufacturer');*/

        $this->installer->endSetup();
    }
}