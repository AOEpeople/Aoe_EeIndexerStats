<?php

require_once 'Mage/Index/controllers/Adminhtml/ProcessController.php';

class Aoe_EeIndexerStats_Adminhtml_ProcessController extends Mage_Index_Adminhtml_ProcessController
{
    public function setInvalidAction()
    {
        $tablenames = $this->getRequest()->getParam('aoeeeindexerstats');
        foreach ($tablenames as $tablename) {
            $this->_getApi()->invalidate($tablename);
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Invalidated ' . $tablename));
        }
        $this->_redirect('*/*/list');
    }

    public function setValidAction()
    {
        $tablenames = $this->getRequest()->getParam('aoeeeindexerstats');
        foreach ($tablenames as $tablename) {
            $this->_getApi()->validate($tablename);
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Validated ' . $tablename));
        }
        $this->_redirect('*/*/list');
    }

    public function cleanupAction()
    {
        $tablenames = $this->getRequest()->getParam('aoeeeindexerstats');
        foreach ($tablenames as $tablename) {
            $this->_getApi()->cleanup($tablename);
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Cleaned up ' . $tablename));
        }
        $this->_redirect('*/*/list');
    }

    public function resetAction($invalidate = false)
    {
        $tablenames = $this->getRequest()->getParam('aoeeeindexerstats');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach ($tablenames as $tablename) {
            if ($invalidate) {
                $this->_getApi()->resetAndInvalidate($tablename);
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Reset and invalidated "' . $tablename . '"'));
            } else {
                $this->_getApi()->reset($tablename);
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Reset "' . $tablename . '"'));
            }
        }
        $this->_redirect('*/*/list');
    }

    public function resetAndInvalidateAction()
    {
        return $this->resetAction(true);
    }

    /**
     * Return the API model for centralized processing logic
     * @return Aoe_EeIndexerStats_Model_Client_Api
     */
    protected function _getApi()
    {
        return Mage::getSingleton('Aoe_EeIndexerStats/client_api');
    }

}
