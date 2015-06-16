<?php

class Aoe_EeIndexerStats_Block_Adminhtml_IndexerStats_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Mass-action block
     *
     * @var string
     */
    protected $_massactionBlockName = 'Aoe_EeIndexerStats/adminhtml_indexerStats_grid_massaction';

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('indexer_eestats_grid');
        $this->_filterVisibility = false;
        $this->_pagerVisibility  = false;
    }

    /**
     * Prepare grid collection
     *
     * @return Mage_Index_Block_Adminhtml_Process_Grid
     */
    protected function _prepareCollection()
    {
        $helper = Mage::helper('Aoe_EeIndexerStats'); /* @var $helper Aoe_EeIndexerStats_Helper_Data */
        $this->setCollection($helper->getIndexerCollection());
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Index_Block_Adminhtml_Process_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('changelog_name', array(
            'header'    => Mage::helper('index')->__('Changelog Name'),
            'align'     => 'left',
            'index'     => 'changelog_name',
            'sortable'  => false,
        ));

        $this->addColumn('index_option', array(
            'header'    => Mage::helper('index')->__('Index Option'),
            'align'     => 'left',
            'index'     => 'tablename',
            'sortable'  => false,
            'width'     => '200',
            'frame_callback' => array($this, 'decorateIndexOption')
        ));

        $this->addColumn('tablename', array(
            'header'    => Mage::helper('index')->__('Table Name'),
            'align'     => 'left',
            'index'     => 'tablename',
            'sortable'  => false,
        ));

        $this->addColumn('current_version_id', array(
            'header'    => Mage::helper('index')->__('Current version id'),
            'align'     => 'right',
            'index'     => 'current_version_id',
            'sortable'  => false,
        ));

        $this->addColumn('last_version_id', array(
            'header'    => Mage::helper('index')->__('Last version id'),
            'align'     => 'right',
            'index'     => 'last_version_id',
            'sortable'  => false,
        ));

        $this->addColumn('count_processed', array(
            'header'    => Mage::helper('index')->__('Count processed'),
            'align'     => 'right',
            'index'     => 'count_processed',
            'sortable'  => false,
        ));

        $this->addColumn('count_unprocessed', array(
            'header'    => Mage::helper('index')->__('Count unprocessed'),
            'align'     => 'right',
            'index'     => 'count_unprocessed',
            'sortable'  => false,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('index')->__('Status'),
            'align'     => 'center',
            'index'     => 'status',
            'sortable'  => false,
            'width'     => '200',
            'frame_callback' => array($this, 'decorateStatus')
        ));

        parent::_prepareColumns();

        return $this;
    }

    /**
     * Decorate status column values
     *
     * @param string $value
     * @param Varien_Object $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool $isExport
     *
     * @return string
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        switch ($row->getStatus()) {
            case Enterprise_Mview_Model_Metadata::STATUS_VALID: $level = 'notice'; $text = 'Valid'; break;
            case Enterprise_Mview_Model_Metadata::STATUS_INVALID: $level = 'critical'; $text = 'Invalid'; break;
            case Enterprise_Mview_Model_Metadata::STATUS_IN_PROGRESS: $level = 'minor'; $text = 'In Progress'; break;
            case 'disabled': $level = 'disabled'; $text = '[Disabled]'; break;
            default: Mage::throwException('Invalid status');
        }
        return sprintf('<span class="grid-severity-%s"><span>%s</span></span>', $level, $this->__($text));
    }

    public function decorateIndexOption($value, $row, $column, $isExport)
    {
        $option = Mage::helper('Aoe_EeIndexerStats')->getIndexOption($value);
        if ($option) {
            $level = ($option['value'] == 1) ? 'critical' : 'notice';
            return sprintf('<span class="grid-severity-%s"><span>%s</span></span>', $level, $option['label']);
        }
        return '';
    }

    /**
     * Add mass-actions to grid
     *
     * @return Mage_Index_Block_Adminhtml_Process_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('tablename');
        $this->getMassactionBlock()->setFormFieldName('aoeeeindexerstats');

        $this->getMassactionBlock()->addItem('setInvalid', array(
            'label'    => Mage::helper('Aoe_EeIndexerStats')->__('Invalidate (will trigger full reindex on next run)'),
            'url'      => $this->getUrl('*/*/setInvalid'),
            'selected' => false,
        ));

        $this->getMassactionBlock()->addItem('cleanup', array(
            'label'    => Mage::helper('Aoe_EeIndexerStats')->__('Cleanup'),
            'url'      => $this->getUrl('*/*/cleanup'),
            'selected' => false,
        ));

        $this->getMassactionBlock()->addItem('reset', array(
            'label'    => Mage::helper('Aoe_EeIndexerStats')->__('Reset'),
            'url'      => $this->getUrl('*/*/reset'),
            'selected' => false,
        ));

        $this->getMassactionBlock()->addItem('resetAndInvalidate', array(
            'label'    => Mage::helper('Aoe_EeIndexerStats')->__('Reset and Invalidate'),
            'url'      => $this->getUrl('*/*/resetAndInvalidate'),
            'selected' => false,
        ));

        return $this;
    }
}
