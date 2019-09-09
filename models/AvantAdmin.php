<?php

class AvantAdmin
{
    public static function emitAdminLinksHtml($itemId, $class, $newWindow, $suffix = '')
    {
        $html = '';
        $target = $newWindow ? ' target="_blank"' : '';
        $class .= ' ' . 'admin-links';

        $html .= "<div class='$class'>";
        $html .= '<a href="' . admin_url('/avant/show/' . $itemId) . '"' . $target . '>' . __('View') . '</a> ';
        $html .= ' | <a href="' . admin_url('/items/edit/' . $itemId) . '"' . $target . '>' . __('Edit') . '</a>';
        $html .= ' | <a href="' . admin_url('/avant/relationships/' . $itemId) . '"' . $target . '>' . __('Relationships') . '</a>';
        $html .= $suffix;
        $html .= '</div>';

        return $html;
    }

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

        if (!$isLoggedIn || !($user->role == 'super' || $user->role == 'admin'))
        {
            // Either no user is logged in, or the user is not an admin. Hide elements with class admin-user.
            // Note that this is the permission required to see Simple Pages that are not published.
            echo ".admin-user{display:none;}";
        }

        echo '</style>'. PHP_EOL;
    }

    public static function emitFlagItemAsRecent($itemId, $recentlyViewedItems)
    {
        if (array_key_exists($itemId, $recentlyViewedItems))
        {
            $flagged = ' flagged';
            $tooltip = __('Remove from recently visited items list');
        }
        else
        {
            $flagged = '';
            $tooltip = __('Add to recently visited items list');
        }

        return "<span data-id='$itemId' class='recent-item-flag$flagged' title='$tooltip'>&nbsp;&nbsp;<a></a></span>";
    }

    public static function emitRecentlyViewedItems($contextIsRelationshipsEditor, $excludeIdentifier = '')
    {
        $html = '';

        $recentlyViewedItems = AvantAdmin::getRecentlyViewedItems();
        $count = count($recentlyViewedItems);
        $clearAll = $count == 0 ? '' : "<a class='recent-items-clear-all'>" . __('Clear all') . '</a>';

        $findUrl = AvantAdmin::getRecentlyViewedItemsSearchUrl($recentlyViewedItems);
        $searchResultsLink = $count == 0 ? '' : "<a href='$findUrl' class='recent-items-search-results' target='_blank'>" . __('Show as search results') . '</a>';

        $html .= '<div id="recent-items-section">';
        $html .= '<div class="recent-items-title">';
        $html .= __('Recently Viewed Items (%s)', $count);
        $html .= $searchResultsLink;
        $html .= $clearAll;
        $html .= '</div>';

        if ($count == 0)
        {
            $html .= '<div class="recent-items-message">' . __('Your recently viewed items list has been cleared.') . '</div>';
        }
        else
        {
            $html .= '<div id="recent-items">';

            foreach ($recentlyViewedItems as $recentItemId => $recentItemIdentifier)
            {
                if ($recentItemIdentifier == $excludeIdentifier)
                    continue;

                $recentItem = ItemMetadata::getItemFromId($recentItemId);
                $itemPreview = new ItemPreview($recentItem);
                $thumbnail = $itemPreview->emitItemThumbnail();

                $title = $contextIsRelationshipsEditor ? ItemMetadata::getItemTitle($recentItem) : $itemPreview->emitItemTitle(true);

                $type = ItemMetadata::getElementTextForElementName($recentItem, 'Type');
                $subject = ItemMetadata::getElementTextForElementName($recentItem, 'Subject');
                $metadata = "<div class='recent-item-metadata'><span>Type:</span>$type&nbsp;&nbsp;&nbsp;&nbsp;<span>Subject:</span>$subject</div>";

                $removeTooltip = __('Remove item from this list (does not delete the item)');
                $removeLink = "<a class='recent-item-remove' data-id='$recentItemId' data-identifier='$recentItemIdentifier' title='$removeTooltip'>" . __('Remove') . '</a>';

                $html .= "<div id='row-$recentItemIdentifier' class='recent-item-row'>";
                $html .= "<div class='recent-item-thumbnail' data-identifier='$recentItemIdentifier'>$thumbnail</div>";
                $html .= "<div class='recent-item'>";

                $addButton = '';
                if ($contextIsRelationshipsEditor)
                {
                    $addButton = "<button type='button' class='action-button recent-item-add' data-identifier='$recentItemIdentifier'>" . __('Add') . "</button>";
                }
                $html .= "<div class='recent-item-title'>$addButton$title</div>";

                $html .= "<div class='recent-item-identifier' data-identifier='$recentItemIdentifier'>$recentItemIdentifier$metadata</div>";

                if (AvantCommon::userIsAdmin())
                {
                    $html .= AvantAdmin::emitAdminLinksHtml($recentItemId, '', !$contextIsRelationshipsEditor, ' | ' . $removeLink);
                }
                else
                {
                    $html .= '<div class="admin-links">' . $removeLink . '</div>';
                }
                $html .= '</div>'; // recent-item

                $html .= '</div>'; // recent-item-row
            }

            $html .= '</div>'; // recent-items
        }
        $html .= '</div>'; // recent-items-section

        return $html;
    }

    public static function emitS3Link($identifier)
    {
        $bucket = S3Config::getOptionValueForBucket();
        $console = S3Config::getOptionValueForConsole();
        $path = S3Config::getOptionValueForPath();
        $region = S3Config::getOptionValueForRegion();
        $id = intval($identifier);
        $folder = $id - ($id % 1000);
        $link = "<a href='$console/$bucket/$path/$folder/$identifier/?region=$region&tab=overview' class='cloud-storage-link' target='_blank'>S3</a>";
        return $link;
    }

    public static function getCustomItemTypeId()
    {
        // In a standard Omeka implementation the admin choose the item type for an item from a list. They choose it when
        // they create a new item and whenever they edit an item's type-specific (non Dublin Core) elements. That's an
        // extra step for the admin, but allows them to use different item types. With AvantAdmin, there is only one item
        // type. The admin adds it as part of their initial Omeka setup and specifies its name on the AvantAdmin configuration
        // page. This function gets called by the logic that would normally operate on the admin's selection from the Item Types
        // list. Note that the admin could delete all but their one custom item type, but this function assumes that there
        // are others that Omeka automatically installed. It finds returns the one configured for AvantAdmin.
        return get_option(AdminConfig::OPTION_ITEM_TYPE);;
    }

    public static function getRecentlyViewedItems($excludeIdentifier = '')
    {
        $cookieValue = isset($_COOKIE['ITEMS']) ? $_COOKIE['ITEMS'] : '';
        $recentItemIds = empty($cookieValue) ? array() : explode(',', $cookieValue);

        $recentlyViewedItems = array();

        foreach ($recentItemIds as $recentItemId)
        {
            if (intval($recentItemId) == 0)
            {
                // This should never happen, but check in case the cookie is somehow corrupted.
                continue;
            }

            $recentItem = ItemMetadata::getItemFromId($recentItemId);

            if (empty($recentItem))
            {
                // Ignore any items that no longer exist.
                continue;
            }

            $recentIdentifier = ItemMetadata::getItemIdentifier($recentItem);
            if ($recentIdentifier == $excludeIdentifier)
            {
                continue;
            }

            $recentlyViewedItems[$recentItemId] = $recentIdentifier;
        }

        return $recentlyViewedItems;
    }

    public static function getRecentlyViewedItemsSearchUrl($recentlyViewedItems)
    {
        $identifierList = '';
        foreach ($recentlyViewedItems as $recentItemId => $recentItemIdentifier)
        {
            if (!empty($identifierList))
                $identifierList .= '|';
            $identifierList .= $recentItemIdentifier;
        }
        $findUrl = ItemSearch::getAdvancedSearchUrl(ItemMetadata::getIdentifierElementId(), $identifierList, 'contains');
        return $findUrl;
    }

    public static function setItemType($item)
    {
        if (!empty($item['item_type_id']))
            return;

        // Explicitly set the item_type_id for a newly added item. Normally in Omeka the admin
        // chooses the item type from a dropdown list, but AvantAdmin hides that list.
        $item['item_type_id'] = AvantAdmin::getCustomItemTypeId();;
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