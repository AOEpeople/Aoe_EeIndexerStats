<?php
/**
 * Provide an API interface for easy invalidation (etc) of indexers
 *
 * @category Mage
 * @package  Aoe_EeIndexerStats
 */
class Aoe_EeIndexerStats_Model_Client_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Invalidate indexer(s)
     * @param  array|string $tableNames
     * @return bool
     */
    public function invalidate($tableNames)
    {
        return $this->_controlAction($tableNames, function($tableName, $metadata) {
            $metadata->setInvalidStatus()->save();
        });
    }

    /**
     * Validate indexer(s)
     * @param  array|string $tableNames
     * @return bool
     */
    public function validate($tableNames)
    {
        return $this->_controlAction($tableNames, function($tableName, $metadata) {
            $metadata->setValidStatus()->save();
        });
    }

    /**
     * Clean up statistics for indexer(s)
     * @param  array|string $tableNames
     * @return bool
     */
    public function cleanup($tableNames)
    {
        return $this->_controlAction($tableNames, function($tableName, $client) {
            $client->execute('enterprise_mview/action_changelog_clear');
        }, true);
    }

    /**
     * Reset indexer(s)
     * @param  array|string $tableNames
     * @return bool
     */
    public function reset($tableNames)
    {
        return $this->_reset($tableNames, false);
    }

    /**
     * Reset indexer(s)
     * @param  array|string $tableNames
     * @return bool
     */
    public function resetAndInvalidate($tableNames)
    {
        return $this->_reset($tableNames, true);
    }

    /**
     * Reset indexer(s) and invalidate at the same time
     * @param  array|string $tableNames
     * @param  boolean      $invalidate
     * @return bool
     */
    protected function _reset($tableNames, $invalidate = false)
    {
        return $this->_controlAction($tableNames, function($tableName, $metadata) use ($invalidate) {
            /** @var Aoe_EeIndexerStats_Model_Changelog $changelog */
            $changelog = Mage::getModel('Aoe_EeIndexerStats/changelog', array(
                'metadata'   => $metadata,
                'connection' => Mage::getSingleton('core/resource')->getConnection('core_write')
            ));
 
            $changelog->resetChangeLog();

            if ($invalidate) {
                $metadata->setInvalidStatus()->save();
            }
        });
    }

    /**
     * Take table names and a callback method, loop through each table name and pass the vars to the callback
     * @param  array|string $tableNames   Tables to process
     * @param  callable     $callback     Method to pass control objects to
     * @param  bool         $returnClient It true, the Client model will be passed to the callback, otherwise the
     *                                    metadata model will
     * 
     * @throws Mage_Api_Exception         On failure
     * @return true                       On success
     */
    protected function _controlAction($tableNames, callable $callback, $returnClient = false)
    {
        // Handle arrays or strings
        if (!is_array($tableNames)) {
            $tableNames = array($tableNames);
        }

        try {
            foreach ($tableNames as $tableName) {
                /** @var Enterprise_Mview_Model_Client $client */
                $client = Mage::getModel('enterprise_mview/client');

                // Backward compatibility with EE < 1.14.2.0
                $initMethod = (method_exists($client, 'initByTableName')) ? 'initByTableName' : 'init';
                $client->{$initMethod}($tableName);

                /** @var Enterprise_Mview_Model_Metadata $metadata */
                $metadata = $client->getMetadata();

                // Trigger callback method
                $object = ($returnClient) ? $client : $metadata;
                $callback($tableName, $object);
            }
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return true;
    }
}
