<?php

class ItemHistory
{
    protected static function formatHistoryDate($date)
    {
        $date = new DateTime($date);
        $date->setTimezone(new DateTimeZone("America/New_York"));
        return $date->format('Y-n-j, g:i a');
    }

    public static function getMostRecentUserName($itemId)
    {
        $db = get_db();
        $userName = '';
        $adminLog = get_db()->getTable('AdminLogs')->getAdminLog($itemId);
        if (!empty($adminLog))
        {
            $log = json_decode($adminLog['log'], true);
            $userId = $log[0]['user'];
            $userName = self::getUserName($userId);
        }
        return $userName;
    }

    public static function getUserName($userId)
    {
        $db = get_db();
        $user = $db->getTable('User')->find($userId);
        $userName = $user ? $user->username : '';
        return $userName;
    }

    public static function logItemSave($itemId)
    {
        if (AvantCommon::batchEditing())
        {
            // Ignore Saves that occur as part of a batch editing process.
            return;
        }

        $userId = current_user()->id;

        $date = new DateTime();
        $date->getTimestamp();

        $adminLog = get_db()->getTable('AdminLogs')->getAdminLog($itemId);
        $entry = array('user' => $userId, 'saved' => $date->format('Y-m-d H:i:s'));

        if (empty($adminLog))
        {
            $log = array();
            $adminLog = new AdminLogs();
            $adminLog['item_id'] = $itemId;
        }
        else
        {
            $log = json_decode($adminLog['log'], true);
        }

        $log[] = $entry;
        $adminLog['log'] = json_encode($log);
        $adminLog->save();
    }

    public static function showItemHistory($item)
    {
        $db = get_db();
        $ownerId = $item->owner_id;

        // Get the name of the item's owner accounting for the possibility of that user's account having been deleted.
        $userName = self::getUserName($ownerId);
        $dateAdded = $item->added;

        $history = "<div><b>Created</b></div>$userName: " . self::formatHistoryDate($dateAdded);

        $adminLog = $db->getTable('AdminLogs')->getAdminLog($item->id);
        if (!empty($adminLog))
        {
            $history .= "</br></br><div><b>Saved</b></div>";
            $log = json_decode($adminLog['log'], true);
            foreach ($log as $entry)
            {
                $userId = $entry['user'];
                $saved = $entry['saved'];
                $userName = self::getUserName($userId);
                $dateSaved = self::formatHistoryDate($saved);
                $history .= "<div>$userName : $dateSaved</div>";
            }
        }

        $html =  "<div class='item-owner panel'><h4>Item History</h4><p>$history</p></div>";
        echo $html;
    }
}