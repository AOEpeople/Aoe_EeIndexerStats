<?php

require_once 'Mage/Index/controllers/Adminhtml/ProcessController.php';

class Aoe_EeIndexerStats_Adminhtml_ProcessController extends Mage_Index_Adminhtml_ProcessController
{

    public function setInvalidAction() {
        $tablenames = $this->getRequest()->getParam('aoeeeindexerstats');
        foreach ($tablenames as $tablename) {
            $client = Mage::getModel('enterprise_mview/client'); /* @var $client Enterprise_Mview_Model_Client */
            $client->init($tablename);
            $metadata = $client->getMetadata();
            $metadata->setInvalidStatus();
            $metadata->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Invalidated ' . $tablename));
        }
        $this->_redirect('*/*/list');
    }

    public function setValidAction() {
        $tablenames = $this->getRequest()->getParam('aoeeeindexerstats');
        foreach ($tablenames as $tablename) {
            $client = Mage::getModel('enterprise_mview/client'); /* @var $client Enterprise_Mview_Model_Client */
            $client->init($tablename);
            $metadata = $client->getMetadata();
            $metadata->setValidStatus();
            $metadata->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Invalidated ' . $tablename));
        }
        $this->_redirect('*/*/list');
    }

    public function cleanupAction() {
        $tablenames = $this->getRequest()->getParam('aoeeeindexerstats');
        foreach ($tablenames as $tablename) {
            $client = Mage::getModel('enterprise_mview/client'); /* @var $client Enterprise_Mview_Model_Client */
            $client->init($tablename);
            $client->execute('enterprise_mview/action_changelog_clear');
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Cleaned up ' . $tablename));
        }
        $this->_redirect('*/*/list');
    }

    public function resetAction($invalidate=false) {
        $tablenames = $this->getRequest()->getParam('aoeeeindexerstats');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach ($tablenames as $tablename) {
            $client = Mage::getModel('enterprise_mview/client'); /* @var $client Enterprise_Mview_Model_Client */
            $client->init($tablename);
            $metadata = $client->getMetadata();
            $changelog = Mage::getModel('Aoe_EeIndexerStats/changelog', array(
                'metadata' => $metadata,
                'connection' => $connection
            )); /* @var $changelog Aoe_EeIndexerStats_Model_Changelog */
            $changelog->resetChangeLog();
            if ($invalidate) {
                $metadata->setInvalidStatus();
                $metadata->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Reset and invalidated' . $tablename));
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Aoe_EeIndexerStats')->__('Reset ' . $tablename));
            }
        }
        $this->_redirect('*/*/list');
    }

    public function resetAndInvalidateAction() {
        return $this->resetAction(true);
    }

}