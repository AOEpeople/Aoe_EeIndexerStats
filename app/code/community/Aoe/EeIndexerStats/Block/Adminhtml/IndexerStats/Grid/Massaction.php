<?php

class Aoe_EeIndexerStats_Block_Adminhtml_IndexerStats_Grid_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
{
    /**
     * Get ids for only visible indexers
     *
     * @return string
     */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        $ids = array();
        foreach ($this->getParentBlock()->getCollection() as $indexer) { /* @var $indexer Varien_Object */
            $ids[] = $indexer->getTablename();
        }

        return implode(',', $ids);
    }
}
