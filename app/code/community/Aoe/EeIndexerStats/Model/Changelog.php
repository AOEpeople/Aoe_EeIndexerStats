<?php

class Aoe_EeIndexerStats_Model_Changelog extends Enterprise_Index_Model_Changelog
{
    public function getUnprossedCount()
    {
        $select = $this->_connection->select()
            ->from(array('changelog' => $this->_metadata->getChangelogName()), array())
            ->where('version_id > ?', $this->_metadata->getVersionId())
            ->columns('COUNT(*)');
        return $this->_connection->fetchOne($select);
    }

    public function getProssedCount()
    {
        $select = $this->_connection->select()
            ->from(array('changelog' => $this->_metadata->getChangelogName()), array())
            ->where('version_id <= ?', $this->_metadata->getVersionId())
            ->columns('COUNT(*)');
        return $this->_connection->fetchOne($select);
    }

    public function resetChangeLog()
    {
        $this->_connection->truncateTable($this->_metadata->getChangelogName());
        $this->_metadata->setVersionId(0)->save();
    }
}
