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

    public static function emitFlagItemAsRecent($itemId, $recentlyViewedItemIds)
    {
        if (in_array($itemId, $recentlyViewedItemIds))
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

    public static function emitRecentlyViewedItems($recentlyViewedItems, $excludeItemId = '', $allowedItems = array(), $alreadyAddedItems = array())
    {
        $contextIsRelationshipsEditor = !empty($excludeItemId);

        $html = '';

        $count = count($recentlyViewedItems);

        // Deal with the case where the only recently viewed item is the excluded primary item.
        if ($count == 1 && $contextIsRelationshipsEditor)
        {
            $item = reset($recentlyViewedItems);
            if ($item->id == $excludeItemId)
            {
                $count = 0;
            }
        }

        $clearAll = $count == 0 ? '' : "<a id='recent-items-clear-all'>" . __('Clear all') . '</a>';

        $recentlyViewedItemInfo = array();
        foreach ($recentlyViewedItems as $recentlyViewedItem)
        {
            $identifier = ItemMetadata::getItemIdentifierAlias($recentlyViewedItem);
            $recentlyViewedItemInfo[$recentlyViewedItem->id] = array('item' => $recentlyViewedItem, 'identifier' => $identifier);
        }

        $findUrl = AvantAdmin::getRecentlyViewedItemsSearchUrl($recentlyViewedItemInfo);
        $searchResultsLink = $count == 0 ? '' : "<a href='$findUrl' id='recent-items-as-search-results' target='_blank'>" . __('Show as search results') . '</a>';

        $html .= '<div id="recent-items-section">';
        $html .= '<div class="recent-items-title">';
        $html .= __('Recently Viewed Items') . ' (<span id="recent-items-count">' . $count . '</span>)';
        $html .= $searchResultsLink;
        $html .= $clearAll;
        $html .= '</div>';

        if ($count == 0)
        {
            $html .= '<div class="recent-items-message">' . __('Your recently viewed items list is empty.') . '</div>';
        }
        else
        {
            $html .= '<div id="recent-items">';

            foreach ($recentlyViewedItemInfo as $recentItemId => $info)
            {
                $recentItemIdentifier = $info['identifier'];
                if ($recentItemIdentifier == $excludeItemId)
                    continue;

                $recentItem = $info['item'];
                $itemPreview = new ItemPreview($recentItem);
                $thumbnail = $itemPreview->emitItemThumbnail();

                // Get the title as a link. If it's for the admin view, change to the public view.
                $title = $itemPreview->emitItemTitle(true);
                $title = str_replace('admin/items', 'items', $title);

                $type = ItemMetadata::getElementTextForElementName($recentItem, 'Type');
                if ($contextIsRelationshipsEditor)
                {
                    $type = "<span class='recent-item-type-emphasis'>$type</span>";
                    $subject = ItemMetadata::getElementTextForElementName($recentItem, 'Subject');
                    $metadata = "<div class='recent-item-metadata'><span>Type:</span>$type&nbsp;&nbsp;&nbsp;&nbsp;<span>Subject:</span>$subject</div>";
                }
                else
                {
                    $metadata = "<div class='recent-item-metadata'>$type</div>";
                }

                $removeTooltip = __('Remove item from this list (does not delete the item)');
                $removeLink = "<a class='recent-item-remove' data-id='$recentItemId' title='$removeTooltip'>" . __('Remove') . '</a>';

                $html .= "<div id='row-$recentItemId' class='recent-item-row'>";
                $html .= "<div class='recent-item-thumbnail' data-identifier='$recentItemIdentifier'>$thumbnail</div>";
                $html .= "<div class='recent-item'>";

                $addButton = '';
                if ($contextIsRelationshipsEditor && array_key_exists($recentItemId, $allowedItems))
                {
                    $disabled = in_array($recentItemId, $alreadyAddedItems) ? 'disabled' : '';
                    $addButton = "<button type='button' class='action-button recent-item-add' data-identifier='$recentItemIdentifier' $disabled>" . __('Add') . "</button>";
                }

                $html .= "<div class='recent-item-identifier' data-identifier='$recentItemIdentifier'><span>" . __('Item ') . "</span>$recentItemIdentifier$metadata</div>";

                $html .= "<div class='recent-item-title'>$addButton$title</div>";

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

    public static function getRecentlySelectedRelationships()
    {
        $cookieValue = isset($_COOKIE['RELATIONSHIPS']) ? $_COOKIE['RELATIONSHIPS'] : '';
        $recentRelationshipCodes = empty($cookieValue) ? array() : explode(',', $cookieValue);

        $recentlySelectedRelationships = array();

        foreach ($recentRelationshipCodes as $recentCode)
        {
            $recentlySelectedRelationships[] = $recentCode;
        }

        return $recentlySelectedRelationships;
    }

    public static function getRecentlyViewedItemIds()
    {
        $cookieValue = isset($_COOKIE['RECENT']) ? $_COOKIE['RECENT'] : '';
        $recentItemIds = empty($cookieValue) ? array() : explode(',', $cookieValue);

        $ids = array();

        foreach ($recentItemIds as $recentItemId)
        {
            if (intval($recentItemId) == 0)
            {
                // This should never happen, but check in case the cookie is somehow corrupted.
                continue;
            }
            $ids[] = $recentItemId;
        }

        return $ids;
    }

    public static function getRecentlyViewedItems($excludeItemId = 0)
    {
        $recentlyViewedItemIds = AvantAdmin::getRecentlyViewedItemIds();
        $deletedItemIds = array();

        $recentlyViewedItems = array();
        foreach ($recentlyViewedItemIds as $id)
        {
            if ($id == $excludeItemId)
                continue;

            // Get the item from its Id.
            $item = ItemMetadata::getItemFromId($id);
            if ($item)
            {
                $recentlyViewedItems[$id] = $item;
            }
            else
            {
                // The item does not exist - it must have been deleted since being recently viewed.
                $deletedItemIds[] = $id;
            }
        }

        // Emit a Javascript array of deleted Ids so the client-side code can remove them from the recent items cookie.
        if (count($deletedItemIds) > 0)
        {
            echo '<script>';
            echo 'var deletedRecentItemIds = [];';
            foreach ($deletedItemIds as $id)
            {
                echo "deletedRecentItemIds.push('$id');";
            }
            echo '</script>';
        }

        return $recentlyViewedItems;
    }

    public static function getRecentlyViewedItemsSearchUrl($recentlyViewedItemInfo)
    {
        $identifierList = '';
        foreach ($recentlyViewedItemInfo as $info)
        {
            if (!empty($identifierList))
                $identifierList .= '|';
            $identifierList .= $info['identifier'];
        }
        $identifierElementId = ItemMetadata::getElementIdForElementName(ItemMetadata::getIdentifierAliasElementName());
        $findUrl = ItemSearch::getAdvancedSearchUrl($identifierElementId, $identifierList, 'contains');

        // Limit the search to the local site since recent items are only tracked for the local site.
        $findUrl .= '&site=0';

        return $findUrl;
    }

    public static function requestRemoteAsset($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE );
        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $response = array();
        $response['response-code'] = $responseCode;
        $response['result'] = $result;
        $response['content-type'] = $contentType;

        return $response;
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