<?php

define('CONFIG_LABEL_MAINTENANCE', __('Maintenance'));
define('CONFIG_LABEL_ITEM_TYPE', __('Item Type Name'));

class AdminConfig extends ConfigOptions
{
    const OPTION_MAINTENANCE = 'avantadmin_maintenance';
    const OPTION_ITEM_TYPE = 'avantadmin_item_type';

    public static function getItemTypeName()
    {
        $itemTypeId = get_option(self::OPTION_ITEM_TYPE);
        $itemTypes = get_table_options('ItemType');
        $itemTypeName = $itemTypes[$itemTypeId];
        return $itemTypeName;
    }

    public static function saveConfiguration()
    {
        $typeNameOption = $_POST[AdminConfig::OPTION_ITEM_TYPE];
        $itemTypeExists = false;
        $itemTypes = get_table_options('ItemType');
        $itemTypeId = 0;

        foreach ($itemTypes as $id => $itemTypeName)
        {
            if ($typeNameOption == $itemTypeName)
            {
                $itemTypeExists = true;
                $itemTypeId = $id;
                break;
            }
        }

        if (!$itemTypeExists)
        {
            throw new Omeka_Validate_Exception(__('\'%s\' does not exist. Click the Item Types button at left to see the Item Types.', $typeNameOption));
        }

        set_option(AdminConfig::OPTION_ITEM_TYPE, $itemTypeId);
        set_option(AdminConfig::OPTION_MAINTENANCE, (int)(boolean)$_POST[self::OPTION_MAINTENANCE]);
    }
}
