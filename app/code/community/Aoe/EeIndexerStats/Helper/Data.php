<?php
/**
 * Class Aoe_EeIndexerStats_Helper_Data
 *
 * @author Fabrizio Branca
 * @since 2015-06-15
 */
class Aoe_EeIndexerStats_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $indexOptions = array(
        'catalog_category_product_cat'        => 'default/index_management/index_options/category_product',
        'catalog_category_product_index'      => 'default/index_management/index_options/category_product',
        'enterprise_url_rewrite_category'     => 'default/index_management/index_options/category_url_rewrite',
        'enterprise_url_rewrite_product'      => 'default/index_management/index_options/product_url_rewrite',
        'enterprise_url_rewrite_redirect'     => 'default/index_management/index_options/redirect_url_rewrite',
        'catalog_category_flat'               => 'default/index_management/index_options/category_flat',
        'catalogsearch_fulltext'              => 'default/index_management/index_options/fulltext',
        'catalog_product_flat'                => 'default/index_management/index_options/product_flat',
        'cataloginventory_stock_status'       => 'default/index_management/index_options/product_price_and_stock',
        'catalog_product_index_price'         => 'default/index_management/index_options/product_price_and_stock',
    );


    /**
     * Get indexer collection
     *
     * @return array
     */
    public function getIndexerCollection() {

        $mview = Mage::getSingleton('enterprise_mview/client'); /* @var $mview Enterprise_Mview_Model_Client */
        $collection = Mage::getModel('enterprise_mview/metadata')->getCollection(); /* @var $collection Enterprise_Mview_Model_Resource_Metadata_Collection */

        $resource = Mage::getSingleton('core/resource'); /* @var $resource Mage_Core_Model_Resource */

        $stats = array();

        $helper = Mage::helper('enterprise_index'); /* @var $helper Enterprise_Index_Helper_Data */
        foreach ($helper->getIndexers() as $indexerConfig) { /* @var $indexerConfig Mage_Core_Model_Config_Element */
            $tableName = $resource->getTableName((string)$indexerConfig->index_table);
            $stats[$tableName] = array(
                'tablename' => $tableName,
                'model' => (string)$indexerConfig->model,
                'action_model_changelog' => (string)$indexerConfig->action_model->changelog,
                'action_model_all' => (string)$indexerConfig->action_model->all,
                'sort_order' => (string)$indexerConfig->sort_order,
            );
        }

        foreach ($collection as $metadata) { /* @var $metadata Enterprise_Mview_Model_Metadata */
            if (!$metadata->getChangelogName()) {
                continue;
            }

            $changelog = Mage::getModel('Aoe_EeIndexerStats/changelog', array(
                'metadata' => $metadata,
                'connection' => $resource->getConnection('core_read')
            )); /* @var $changelog Aoe_EeIndexerStats_Model_Changelog */

            $tableName = $metadata->getTableName();
            if (!isset($stats[$tableName])) { $stats[$tableName] = array(); }

            $stats[$tableName]['tablename'] = $tableName;
            $stats[$tableName]['changelog_name'] = $metadata->getChangelogName();
            $stats[$tableName]['current_version_id'] = $metadata->getVersionId();
            $stats[$tableName]['last_version_id'] = $changelog->getLastVersionId();
            $stats[$tableName]['count_processed'] = $changelog->getProssedCount();
            $stats[$tableName]['count_unprocessed'] = $changelog->getUnprossedCount();
            if ($tableName == 'catalog_category_flat' && !(bool)(int)Mage::getConfig()->getNode('default/catalog/frontend/flat_catalog_category')) {
                $stats[$tableName]['status'] = 'disabled';
            } elseif ($tableName == 'catalog_product_flat' && !(bool)(int)Mage::getConfig()->getNode('default/catalog/frontend/flat_catalog_product')) {
                $stats[$tableName]['status'] = 'disabled';
            } else {
                $stats[$tableName]['status'] = $metadata->getStatus();
            }
        }

        // convert into collection
        $collection = new Varien_Data_Collection();
        foreach ($stats as $indexer) {
            $collection->addItem(new Varien_Object($indexer));
        }

        return $collection;
    }

    public function getIndexOption($tableName) {
        if (isset($this->indexOptions[$tableName])) {
            $option = (int)Mage::app()->getConfig()->getNode($this->indexOptions[$tableName]);
            $sourceModel = Mage::getSingleton('enterprise_index/system_config_source_update'); /* @var $sourceModel Enterprise_Index_Model_System_Config_Source_Update */
            $options = $sourceModel->toArray();
            $text = $options[$option];
            return array(
                'value' => $option,
                'label' => $text
            );
        }
        return false;
    }

} 