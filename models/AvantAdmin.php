<?php

class AvantAdmin
{
    public static function emitDynamicCss()
    {
        // Dynamically emit CSS for elements that should or should not display for logged in users.

        echo PHP_EOL . '<style>';

        $user = current_user();
        $isLoggedIn = !empty($user);

        // When a user is logged in, hide elements with class logged-out.
        // When no user is logged in, hide elements with class logged-in.
        $class = $isLoggedIn ? '.logged-out' : '.logged-in';
        echo "$class{display:none;}";

        $role = $user->role;
        if (!$isLoggedIn || !($role == 'super' || $role == 'admin'))
        {
            // Either no user is logged in, or the user is not an admin. Hide elements with class admin-user.
            // Note that this is the permission required to see Simple Pages that are not published.
            echo ".admin-user{display:none;}";
        }

        echo '</style>'. PHP_EOL;
    }

    public static function getCustomItemTypeId()
    {
        // In a standard Omeka implementation the admin choose the item type for an item from a list. They choose it when
        // they create a new item and whenever they edit an item's type-specific (non Dublin Core) elements. That's an
        // extra step for the admin, but allows them to use different item types. With AvantAdmin, there is only one item
        // type. The admin adds it as part of their initial Omeka setup and specifies its name on the AvantAdmin configuration
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

    public static function setItemType($item)
    {
        if (!empty($item['item_type_id']))
            return;

        // Explicitly set the item_type_id for a newly added item. Normally in Omeka the admin
        // chooses the item type from a dropdown list, but AvantAdmin hides that list.
        $item['item_type_id'] = AvantAdmin::getCustomItemTypeId();;
    }

    public static function saveConfiguration()
    {
        $typeNameOption = $_POST['avantadmin_type_name'];
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

        set_option('avantadmin_type_name', $typeNameOption);
        set_option('avantadmin_maintenance', (int)(boolean)$_POST['avantadmin_maintenance']);
    }

    public static function showPublicPrivateStatus($item)
    {
        if ($item->public)
        {
            $html =  '<div class="public-item panel">' . __('Public Item') . '</div>';
        }
        else
        {
            $html =  '<div class="private-item panel">' . __('This item is Private') . '</div>';
        }

        echo $html;
    }
}