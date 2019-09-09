const ITEMS_COOKIE = 'ITEMS';
const MAX_RECENT_ITEMS = 100;

function addRecentItemEventListeners()
{
    var recentItemRemove = jQuery('.recent-item-remove');
    var recentItemsClearAll = jQuery('.recent-items-clear-all');
    var recentItemFlag = jQuery('.recent-item-flag');

    recentItemRemove.click(function ()
    {
        var itemIdentifier = jQuery(this).attr('data-identifier');
        var itemId = jQuery(this).attr('data-id');
        var row = jQuery('#row-' + itemIdentifier);
        jQuery(row).hide("slow");
        removeRecentlyVisitedItem(itemId);
    });

    recentItemsClearAll.click(function ()
    {
        if (confirm('Clear all recently visited items?'))
        {
            removeAllItemsFromCookie();
            jQuery('#recent-items').remove();
            jQuery(this).remove();
            window.location.href = document.location.href;
        }
    });


    recentItemFlag.click(function (e)
    {
        var flag = jQuery(this);
        var itemId = flag.attr('data-id');
        if (flag.hasClass('flagged'))
        {
            removeRecentlyVisitedItem(itemId);
            flag.removeClass('flagged');
        }
        else
        {
            addRecentlyVisitedItem(itemId);
            flag.addClass('flagged');
        }
    });
}

function addRecentlyVisitedItem(itemId)
{
    var oldItemIds = retrieveRecentItemIds();
    var newItemIds = '';
    if (oldItemIds.length === 0)
    {
        newItemIds = itemId;
    }
    else
    {
        // Put the new Id at index 0, and copy the old Ids after it.
        newItemIds = [itemId];
        var count = 1;

        for (id of oldItemIds)
        {
            if (itemId === id)
            {
                // The Id was already in the stack. Ignore it since it's now on the top.
                continue;
            }
            newItemIds.push(id);
            count += 1;

            // Only show the last dozen Ids.
            if (count >= MAX_RECENT_ITEMS)
                break;
        }
        newItemIds = newItemIds.join(',');
    }

    Cookies.set(ITEMS_COOKIE, newItemIds, {expires: 14});
}

function removeAllItemsFromCookie()
{
    Cookies.set(ITEMS_COOKIE, '', {expires: 14});
}

function removeRecentlyVisitedItem(itemId)
{
    var oldItemIds = retrieveRecentItemIds();
    var newItemIds = [];
    for (id of oldItemIds)
    {
        if (itemId === id)
        {
            // Remove the item bu not adding it to the new list.
            continue;
        }
        newItemIds.push(id);
    }
    newItemIds = newItemIds.join(',');

    Cookies.set(ITEMS_COOKIE, newItemIds, {expires: 14});
}

function retrieveRecentItemIds()
{
    var value = Cookies.get(ITEMS_COOKIE);
    var itemIds = [];
    if (value !== undefined)
    {
        itemIds = value.split(',');
    }

    return itemIds;
}

jQuery(document).ready(function ()
{
    addRecentItemEventListeners();
});
