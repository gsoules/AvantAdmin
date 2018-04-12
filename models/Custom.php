<?php

class Custom
{
    public static function getCustomItemTypeId()
    {
        // In a standard Omeka implementation the admin choose the item type for an item from a list. They choose it when
        // they create a new item and whenever they edit an item's type-specific (non Dublin Core) elements. That's an
        // extra step for the admin, but allows them to use different item types. With AvantCustom, there is only one item
        // type. The admin adds it as part of their initial Omeka setup and specifies its name on the AvantCustom configuration
        // page. This function gets called by the logic that would normally operate on the admin's selection from the Item Types
        // list. Note that the admin could delete all but their one custom item type, but this function assumes that there
        // are others that Omeka automatically installed. It finds the right one and returns its ID.

        $itemTypes = get_db()->getTable('ItemType')->findAll();
        $customItemTypeName = get_option('avantadmin_type_name');

        // Use the first item type as the default in case the user specified an invalid name in the configuration options.
        $customItemTypeId = $itemTypes[0]->id;

        foreach ($itemTypes as $itemType)
        {
            if ($itemType->name == $customItemTypeName)
            {
                $customItemTypeId = $itemType->id;
                break;
            }
        }

        return $customItemTypeId;
    }
}