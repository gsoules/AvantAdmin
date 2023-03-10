<?php

class ItemHistory
{
    const MAX_HISTORY = 10;
    const LOG_DATE_FORMAT = 'Y-m-d H:i:s';

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


    public static function formatHistoryDate($date)
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

    public static function logItemDelete($item)
    {
        self::logItemAction($item, 'deleted');
    }

    public static function logItemSave($item)
    {
        self::logItemAction($item, 'saved');
    }

    public static function logItemAction($item, $action)
    {
        // Get current timestamp.
        $date = new DateTime();
        $date->getTimestamp();

        // Determine how many seconds it's been since this item was created.
        $dateNow = $date->format(self::LOG_DATE_FORMAT);
        $addedDate = new DateTime($item->added);
        $seconds = strtotime($dateNow) - strtotime($addedDate->format(self::LOG_DATE_FORMAT));

        if ($seconds <= 3)
        {
            // This item was just added within the last 2 seconds. Don't log as a Save.
            return;
        }

        // Get the user for this action. If there is none, the action was performed via a remote
        // request such as when AvantHybrid adds, updates, or deletes items.
        $user = current_user();
        $userId = $user ? $user->id : 0;

        // Create a log entry for this action.
        $adminLog = get_db()->getTable('AdminLogs')->getAdminLog($item->id);

        $identifier = ItemMetadata::getItemIdentifier($item);
        if ($action == 'saved')
            $newEntry = array('user' => $userId, 'identifier' => $identifier, 'saved'=> $dateNow);
        else
            $newEntry = array('user' => $userId, 'identifier' => $identifier, 'deleted'=> $dateNow);

        if (empty($adminLog))
        {
            // This item has no history. Create the first entry.
            $log = array();
            $adminLog = new AdminLogs();
            $adminLog['item_id'] = $item->id;
            $log[] = $newEntry;
        }
        else
        {
            // Get the item's past history.
            $log = json_decode($adminLog['log'], true);

            // Determine if the current user was the last person to save this item.
            $mostRecentEntry = $log[0];
            if ($action == 'saved' && $userId == $mostRecentEntry['user'])
            {
                // This user is saving the item again. Just update the timestamp for the most recent entry.
                $log[0]['saved'] = $newEntry['saved'];
            }
            else
            {
                // A different user is saving the item or the item is being deleted. Put the new entry at the top of the log.
                array_unshift($log, $newEntry);
            }

            // Trim the log if it has grown beyond the max number of entries.
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

        // Update the log in the database.
        $adminLog['log'] = json_encode($log);
        $adminLog->save();
    }

    public static function showItemHistory($item)
    {
        $db = get_db();
        $ownerId = $item->owner_id;

        if ($ownerId)
            $userName = self::getUserName($ownerId);
        else
            $userName = __('[No owner]');

        $dateAdded = $item->added;

        $history = "<div><b>" . __('Created') . "</b></div>$userName : " . self::formatHistoryDate($dateAdded);

        $adminLog = $db->getTable('AdminLogs')->getAdminLog($item->id);
        $history .= "</br></br><div><b>" . __('Saved') . "</b></div>";
        if (empty($adminLog))
        {
            $modified = $item->modified;
            $dateModified = self::formatHistoryDate($modified);
            $history .= "<div>$dateModified</div>";
        }
        else
        {
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

        $html =  "<div class='item-owner panel'><h4>" . __('Item History') . "</h4><p>$history</p></div>";
        echo $html;
    }
}
