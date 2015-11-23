<?php

class Aoe_EeIndexerStats_Model_Observer
{
    /**
     * Reset and invalidate enterprise indexers
     *
     * @param  Aoe_Scheduler_Model_Schedule $schedule
     * @return self
     * @throws Enterprise_Index_Exception
     */
    public function resetAndInvalidateEeIndexer(Aoe_Scheduler_Model_Schedule $schedule)
    {
        if ($parameters = $schedule->getParameters()) {
            $tableNames = json_decode($parameters);
            Mage::getSingleton('Aoe_EeIndexerStats/client_api')->resetAndInvalidate($tableNames);
        } else {
            throw new Enterprise_Index_Exception('Set the cron parameters (Ex. ["enterprise_url_rewrite_category","catalogsearch_fulltext"]) to reset and invalidate the indexers');
        }
        return $this;
    }

    /**
     * Reindex community indexers
     *
     * @param  Aoe_Scheduler_Model_Schedule $schedule
     * @return self
     */
    public function reindexCeIndexer(Aoe_Scheduler_Model_Schedule $schedule)
    {
        if ($parameters = $schedule->getParameters()) {
            $codes = json_decode($parameters);
            foreach ($codes as $code) {
                /* @var $process Mage_Index_Model_Process */
                if ($process = Mage::getModel('index/indexer')->getProcessByCode($code)) {
                    $process->reindexAll();
                }
            }
        } else {
            throw new Enterprise_Index_Exception('Set the cron parameters (Ex. ["catalog_category_flat","catalog_product_flat"]) to run the indexing');
        }
        return $this;
    }
}
