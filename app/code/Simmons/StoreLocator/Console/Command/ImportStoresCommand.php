<?php

namespace Simmons\StoreLocator\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;

class ImportStoresCommand extends Command
{
    const FILE_ARGUMENT = 'filename';
    protected $state;
    protected $moduleDirectory;
    protected $_urlRewriteFactory;
    protected $_objectManager;

    public function __construct(
        State $state,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
    )
    {
        $this->state = $state;
        $this->moduleDirectory = $moduleReader->getModuleDir('etc', 'Simmons_StoreLocator') . '/../import/';;
        $this->_urlRewriteFactory = $urlRewriteFactory;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('simmons:storelocator:import')
            ->setDescription('Import the list of Stores used in extension Store Locator')
            ->setDefinition([
                new InputArgument(
                    self::FILE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'filename'
                )
            ]);
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode('adminhtml');
        }
        catch (\Exception $e) {
        }

        if ($input->hasArgument('filename') && !empty($input->getArgument('filename'))) {
            $sourceFile = $input->getArgument('filename');
        } else {
            $sourceFile = 'sample_list.csv';
        }
        $filePath = $this->moduleDirectory . $sourceFile;

        $translationIterator = \Transliterator::createFromRules(':: NFD; :: [:Nonspacing Mark:] Remove; :: NFC; :: Lower();', \Transliterator::FORWARD);
        if (file_exists($filePath)) {
            $fileHandle = fopen($filePath, 'r');
            $keys = ['localtor_id','store_name','address1','address2','city','state','zip_code','phone_number','unused_store_id','store_link','latitude','longitude','unused_new_date','unused_st','unused_sa','unused_simmons_ohm_bt','unused_serta_ohm_bt','unused_ohm_cg','unused_l1','unused_l2','unused_l3','unused_l4','unused_lt','unused_lrn','unused_brand','unused_rep_salesteam','unused_rep_region','unused_rep_branch','unused_rep_territory','unused_sales_channel','unused_market_segment','store_rank','unused_e1','unused_e2','unused_e3','unused_e4','unused_e5','unused_e6','unused_e7','unused_e8','unused_e9','unused_e10','unused_e11','unused_e12','unused_e13','unused_e14','unused_e15','unused_e16','unused_e17'];
            $csvKeys = fgetcsv($fileHandle, 1000, ';', '"', '\\');
            $counter = 0;
            $errorCounter = 0;
            while ($row = fgetcsv($fileHandle, 1000, ';', '"', '\\')) {
                $data = array_combine($keys, $row);
                foreach ($data as $key => $value) {
                    if (strpos($key, 'unused') === 0) {
                        unset($data[$key]);
                    }
                }

                $identifier = $translationIterator->transliterate($data['store_name']);
                $data['identifier'] = preg_replace('/[-\s]+/', '-', $identifier);
                $data['status'] = 1;
                $data['schedule_id'] = 0;
                $data['zoom_level'] = 1;
                $data['address'] = $data['address1'];
                if (!empty($data['address2'])) {
                    $data['address'] .= ' ' . $data['address2'];
                }
                unset($data['address1']);
                unset($data['address2']);
                switch ($data['store_rank']) {
                    case 'Black Diamond Preferred':
                        $data['store_rank'] = 9;
                        break;
                    default:
                        $data['store_rank'] = 1;
                        break;
                }

                $model = $this->_objectManager->create('Netbaseteam\Locator\Model\Locator');
                $model->load($data['localtor_id']);
                if (!$model->getId()) {
                    $model->isObjectNew(true);
                    $model->getResource()->_disablePkAutoIncrement();
                }
                $model->addData($data);

                try {
                    $model->save();
                } catch (\Exception $e) {
                    $errorCounter++;
                    echo $e->getMessage();
                }
                $locatorId = $model->getId();

                if (empty($data['url_rewrite_id'])) {
                    $urlRewriteModel = $this->_urlRewriteFactory->create();
                    $urlRewriteModel->load('locator/' . $data['identifier'] . '-' . $data['localtor_id'], 'request_path');
                    if ($urlRewriteModel->getId()) {
                        $requestPath ='locator/' . $data['identifier'] . '-' . $data['localtor_id'];
                        $data = array(
                            'url_rewrite_id'=>$urlRewriteModel->getId(),
                            'request_path'=>$requestPath,
                            'store_id'=>'1'
                        );
                        $urlRewriteModel->addData($data);
                        $urlRewriteModel->save();
                    } else {
                        unset($urlRewriteModel);
                        $urlRewriteId = $this->createUrlRewrite($data['identifier'], $locatorId);
                        $model->load($locatorId)->setUrlRewriteId($urlRewriteId);
                        $model->save();
                    }
                } else {
                    $urlRewriteModel = $this->_urlRewriteFactory->create();
                    $urlRewriteModel->load($data['localtor_id']);
                    $requestPath ='locator/' . $data['identifier'] . '-' . $data['localtor_id'];
                    $data = array(
                        'url_rewrite_id'=>$urlRewriteModel->getId(),
                        'request_path'=>$requestPath,
                        'store_id'=>'1'
                    );
                    $urlRewriteModel->addData($data);
                    $urlRewriteModel->save();
                }
                $counter++;
                unset($model);
            }
            fclose($fileHandle);
        } else {
            echo "Source file [$sourceFile] does not exist.";
        }

        echo "Processed $counter entries with $errorCounter errors.\n";
    }

    public function createUrlRewrite($identifier,$localtorId){
        $requestPath = 'locator/'.$identifier.'-'.$localtorId;
        $targetPath = 'locator/store/index/id/'.$localtorId;
        $data = array(
            'url_rewrite_id'=>null,
            'entity_type'=>'localtor-view',
            'entity_id' =>$localtorId,
            'request_path'=>$requestPath,
            'target_path'=>$targetPath,
            'store_id'=>'1'
        );

        $urlRewriteModel = $this->_urlRewriteFactory->create();
        $urlRewriteModel->addData($data);
        $urlRewriteModel->save();
        $urlRewriteId = $urlRewriteModel->getId();
        return $urlRewriteId;
    }
}