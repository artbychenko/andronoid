<?php

namespace Andronoid\Base\Setup;

use Andronoid\Base\Setup\InstallData as AndronoidBaseInstallData;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\Data\PageInterface;

/**
 * Andronoid\Base\Setup\UpgradeData
 *
 * @category    Andronoid
 * @package     Andronoid_Base
 */

class UpgradeData implements UpgradeDataInterface
{
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
     * UpgradeData constructor.
     * @param StoreManagerInterface $storeManager
     * @param PageInterfaceFactory $pageFactory
     * @param Config $config
     * @param PageRepositoryInterface $pageRepositoryInterface
     * @param PageInterface $pageInterface
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PageInterfaceFactory $pageFactory,
        Config $config,
        PageRepositoryInterface $pageRepositoryInterface,
        PageInterface $pageInterface,
        BlockFactory $blockFactory
    )
    {
        $this->storeManager = $storeManager;
        $this->pageFactory = $pageFactory;
        $this->config = $config;
        $this->pageRepositoryInterface = $pageRepositoryInterface;
        $this->pageInterface = $pageInterface;
        $this->blockFactory = $blockFactory;
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

        //$this->upgradeToVersion('0.0.2');

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
}