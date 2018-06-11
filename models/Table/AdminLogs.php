<?php

class Table_AdminLogs extends Omeka_Db_Table
{
    public function getAdminLog($itemId)
    {
        $select = $this->getSelect()->where('item_id = ?', $itemId);
        return $this->fetchObject($select);
    }
}
