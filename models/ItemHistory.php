<?php

class ItemHistory
{
    const MAX_HISTORY = 5;

    public static function createAdminLogTable()
    {
        $db = get_db();

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}admin_logs` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `item_id` int(10) unsigned NOT NULL,
            `log` varchar(512) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $db->query($sql);
    }


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
        $newEntry = array('user' => $userId, 'saved' => $date->format('Y-m-d H:i:s'));

        if (empty($adminLog))
        {
            // This item has no history. Create the first entry.
            $log = array();
            $adminLog = new AdminLogs();
            $adminLog['item_id'] = $itemId;
            $log[] = $newEntry;
        }
        else
        {
            // Get the item's past history.
            $log = json_decode($adminLog['log'], true);

            // Determine if the current user was the last to previously save this item.
            $mostRecentEntry = $log[0];
            if ($userId == $mostRecentEntry['user'])
            {
                // This user is saving the item again. Just update the timestamp for the most recent entry.
                $log[0]['saved'] = $newEntry['saved'];
            }
            else
            {
                // A different user is saving the item. Put the new entry at the top of the log.
                array_unshift($log, $newEntry);
            }

            if (count($log) > self::MAX_HISTORY)
            {
                foreach ($log as $index => $entry)
                {
                    if ($index >= self::MAX_HISTORY)
                    {
                        unset($log[$index]);
                    }
                }
            }
        }

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