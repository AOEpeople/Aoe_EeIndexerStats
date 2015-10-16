<?php

class Aoe_EeIndexerStats_Model_Client extends Enterprise_Mview_Model_Client
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_CHANGELOG_CLEAN_DUPLICATE        = 'index_management/index_changelog/clean_duplicate';

    /**
     * Execute action
     *
     * @param string $classPath
     * @param array $args
     * @return Enterprise_Mview_Model_Client
     * @throws Enterprise_Mview_Exception
     */
    public function execute($classPath, array $args = array())
    {
        if (Mage::getStoreConfig(self::XML_PATH_CHANGELOG_CLEAN_DUPLICATE)) {
            if (!is_object($this->_metadata)) {
                throw new Enterprise_Mview_Exception('Metadata should be initialized before action is executed');
            }

            $this->_cleanChangelogTablesBeforeIndexing($this->_metadata);
        }

        return parent::execute($classPath, $args);
    }


    /**
     * Clean duplicate entity data from changelog tables before running the indexing
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     * @return Enterprise_Mview_Model_Client
     */
    protected function _cleanChangelogTablesBeforeIndexing(Enterprise_Mview_Model_Metadata $metadata)
    {
        $_connection = $this->_getDefaultConnection();

        $subSelect = $_connection->select()
            ->from(array('cl' => $metadata->getChangelogName()), array('version_id'=>'MAX(cl.version_id)'))
            ->where('cl.version_id > ?', $metadata->getVersionId())
            ->group('cl.'.$metadata->getKeyColumn());

        $select = $_connection->select()->from($subSelect, array('*'));

        $whereCondition = array(
            $_connection->quoteInto('version_id > ?', $metadata->getVersionId()),
            'version_id NOT IN (' . $select . ')',
        );
        $_connection->delete($metadata->getChangelogName(),  implode(' AND ', $whereCondition));

        return $this;
    }
}