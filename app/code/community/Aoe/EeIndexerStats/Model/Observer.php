<?php

class Aoe_EeIndexerStats_Model_Observer
{
    /**
     * Reset and invalidate enterprise indexers
     *
     * @param Aoe_Scheduler_Model_Schedule $schedule
     * @return Aoe_EeIndexerStats_Model_Observer
     * @throws Enterprise_Index_Exception
     */
    public function resetAndInvalidateEeIndexer(Aoe_Scheduler_Model_Schedule $schedule)
    {
        if ($parameters = $schedule->getParameters()) {
            $tableNames = json_decode($parameters);
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            foreach($tableNames as $tableName) {
                $client = Mage::getModel('enterprise_mview/client'); /* @var $client Enterprise_Mview_Model_Client */
                $client->initByTableName($tableName);
                $metadata = $client->getMetadata();
                $changelog = Mage::getModel('Aoe_EeIndexerStats/changelog', array(
                    'metadata' => $metadata,
                    'connection' => $connection
                )); /* @var $changelog Aoe_EeIndexerStats_Model_Changelog */
                $changelog->resetChangeLog();
                $metadata->setInvalidStatus();
                $metadata->save();
            }
        } else {
            throw new Enterprise_Index_Exception('Set the cron parameters (Ex. ["enterprise_url_rewrite_category","catalogsearch_fulltext"]) to reset and invalidate the indexers');
        }
        return $this;
    }

    /**
     * Reindex community indexers
     *
     * @param Aoe_Scheduler_Model_Schedule $schedule
     * @return Aoe_EeIndexerStats_Model_Observer
     */
    public function reindexCeIndexer(Aoe_Scheduler_Model_Schedule $schedule)
    {
        if ($parameters = $schedule->getParameters()) {
            $codes = json_decode($parameters);
            foreach($codes as $code) {
                if ($process = Mage::getModel('index/indexer')->getProcessByCode($code) ) { /* @var $process Mage_Index_Model_Process */
                    $process->reindexAll();
                }
            }
        } else {
            throw new Enterprise_Index_Exception('Set the cron parameters (Ex. ["catalog_category_flat","catalog_product_flat"]) to run the indexing');
        }
        return $this;
    }
}