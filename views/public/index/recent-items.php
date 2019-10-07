<?php
$pageTitle = __('Recent Items');
echo head(array('bodyclass' => 'index primary-secondary', 'title' => $pageTitle));
$recentlyViewedItems = AvantAdmin::getRecentlyViewedItems();

// Remove any recently viewed items that have been deleted since last viewed.
// Create an array of those items so that the client-side code can remove them from the cookie.
$deletedItemIds = array();
foreach ($recentlyViewedItems as $itemId => $item)
{
    if (!$item)
    {
        $deletedItemIds[] = $itemId;
        unset($recentlyViewedItems[$itemId]);
    }
}

if (count($recentlyViewedItems) > 0)
{
    echo '<script>';
    echo 'var deletedRecentItemIds = [];';
    foreach ($deletedItemIds as $id)
    {
        echo "deletedRecentItemIds.push('$id');";
    }
    echo '</script>';
}

echo AvantAdmin::emitRecentlyViewedItems($recentlyViewedItems);

echo foot();


