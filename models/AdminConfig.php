<?php

define('CONFIG_LABEL_MAINTENANCE', __('Maintenance'));
define('CONFIG_LABEL_TYPE_NAME', __('Item Type Name'));

class AdminConfig extends ConfigOptions
{
    const OPTION_MAINTENANCE = 'avantadmin_maintenance';
    const OPTION_TYPE_NAME = 'avantadmin_type_name';

    public static function saveConfiguration()
    {
        $typeNameOption = $_POST[AdminConfig::OPTION_TYPE_NAME];
        $itemTypeExists = false;
        $itemTypes = get_table_options('ItemType');

        foreach ($itemTypes as $itemTypeName)
        {
            if ($typeNameOption == $itemTypeName)
            {
                $itemTypeExists = true;
                break;
            }
        }

        if (!$itemTypeExists)
        {
            throw new Omeka_Validate_Exception(__('\'%s\' does not exist. Click the Item Types button at left to see the Item Types.', $typeNameOption));
        }

        set_option(AdminConfig::OPTION_TYPE_NAME, $typeNameOption);
        set_option(AdminConfig::OPTION_MAINTENANCE, (int)(boolean)$_POST[self::OPTION_MAINTENANCE]);
    }
}
