<?php
namespace Simmons\StoreLocator\Block\Adminhtml\Locator\Edit\Tab;

use Netbaseteam\Locator\Block\Adminhtml\Locator\Edit\Tab\Main as BaseMain;

class Main extends BaseMain
{
    protected $_storeRank;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Netbaseteam\Locator\Model\Config\Source\Schedule $scheduleConfig,
        \Simmons\StoreLocator\Model\Config\Rank $storeRank,
        array $data = []
    ) {
        $this->_storeRank = $storeRank;
        parent::__construct($context, $registry, $formFactory, $systemStore, $wysiwygConfig, $scheduleConfig, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {

        $model = $this->_coreRegistry->registry('localtor');


        if ($this->_isAllowedAction('Netbaseteam_Locator::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('localtor_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Store Information')]);

        if ($model->getId()) {
            $fieldset->addField('localtor_id', 'hidden', ['name' => 'localtor_id']);
        }

        $fieldset->addField(
            'store_name',
            'text',
            [
                'name' => 'store_name',
                'label' => __('Store Name'),
                'title' => __('Store Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'identifier',
            'text',
            [
                'name' => 'identifier',
                'label' => __('Identifier'),
                'title' => __('Identifier'),
                'required' => true,
                'class'=>'validate-xml-identifier',
                'disabled' => $isElementDisabled
            ]
        );

        $storeRanks = $this->_storeRank->toOptionArray();

        $fieldset->addField(
            'store_rank',
            'select',
            [
                'name' => 'store_rank',
                'label' => __('Store Rank'),
                'title' => __('Store Rank'),
                'options' => $storeRanks,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField('url_rewrite_id', 'hidden', ['name' => 'url_rewrite_id']);

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'options' => ['1' => __('Enable'), '0' => __('Disable')],
                'disabled' => $isElementDisabled
            ]
        );

        $cheduleData = $this->_scheduleConfig->toOptionArray();

        $fieldset->addField(
            'schedule_id',
            'select',
            [
                'name' => 'schedule_id',
                'label' => __('Select Schedule'),
                'title' => __('Select Schedule'),
                'values'=> $cheduleData,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_link',
            'text',
            [
                'name' => 'store_link',
                'label' => __('Store Link'),
                'title' => __('Store Link'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );


        $fieldset->addField(
            'store_image',
            'image',
            [
                'name' => 'store_image',
                'label' => __('Store Image'),
                'title' => __('Store Image'),
                'required'  => false,
                'note'=>__('Allowed file types: jpg, jpeg, gif, png'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'ordering',
            'text',
            [
                'name' => 'ordering',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => false,
                'class' => 'validate-number',
                'disabled' => $isElementDisabled
            ]
        );

        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);

        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'config' =>$wysiwygConfig

            ]
        );

        $this->_eventManager->dispatch('adminhtml_locator_edit_tab_main_prepare_form', ['form' => $form]);

        $dataForm = $model->getData();

        if(!empty($dataForm['store_image'])){
            $dataForm['store_image'] = 'locator/store_image/'. $dataForm['store_image'];
        }

        $form->setValues($dataForm);
        $this->setForm($form);

        $parentOfParent = get_parent_class(get_parent_class($this));

        return $parentOfParent::_prepareForm();
    }
}