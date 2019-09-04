<?php

function emitItemInformation(Omeka_Record_AbstractRecord $item)
{
    $type = ItemMetadata::getElementTextForElementName($item, 'Type');
    $subject = ItemMetadata::getElementTextForElementName($item, 'Subject');
    $title = ItemMetadata::getItemTitle($item);

    $html = '<div id="relationships-editor-grid">';
    $imageUrl = ItemPreview::getImageUrl($item, true, true);
    $html .= "<img class='relationships-editor-image' src='$imageUrl'>";

    $html .= "<div class='relationships-editor-metadata'>";
    $html .= "<div class='relationships-editor-title'>$title</div>";
    $html .= "<div><span class='element-name'>Type:</span> $type</div>";
    $html .= "<div><span class='element-name'>Subject:</span> $subject</div>";
    $html .= "</div>";

    $html .= "<div class='relationships-editor-buttons'>";
    $viewLink = html_escape(admin_url('items/show/' . metadata('item', 'id')));
    $html .= "<a href='$viewLink' class='big beige button'>" . __('View Admin Item') . "</a>";
    $publicLink = html_escape(public_url('items/show/' . metadata('item', 'id')));
    $html .= "<div><a href='$publicLink' class='big blue button'>" . __('View Public Page') . "</a></div>";
    if (is_allowed($item, 'edit'))
    {
        $editLink = link_to_item(__('Edit Item'), array('class' => 'big green button'), 'edit');
        $html .= $editLink;
    }
    $html .= "</div>";

    $html .= "</div>";

    echo $html;
}

$item = get_record_by_id('Item', $itemId);
if (empty($item))
{
    echo "No item found";
    return;
}
set_current_record('Item', $item);

$primaryItemIdentifier = ItemMetadata::getItemIdentifier($item);
$itemTitle = __('Edit Relationships for Item %s', $primaryItemIdentifier);
echo head(array('title' => $itemTitle, 'bodyclass'=>'relationships'));

emitItemInformation($item);

$relatedItemsModel = new RelatedItemsModel($item, $this);
$relatedItems = $relatedItemsModel->getRelatedItems();
$relatedItemsEditor = new RelatedItemsEditor($relatedItemsModel, $item);
$formSelectRelationshipNames = $relatedItemsEditor->getRelationshipNamesSelectList();

?>

<table class="relationships-editor-table">
    <thead>
    <tr>
        <th class="relationship-table-relationship"><?php echo __('Relationship'); ?></th>
        <th class="relationship-table-related-item"><?php echo __('Related&nbsp;Item'); ?></th>
        <th class="relationship-table-related-item-title"><?php echo __('Related Item Title'); ?></th>
        <th class="relationship-table-action"><?php echo __('Action'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($relatedItems as $relatedItem)
    {
        $relatedItemIdentifier = $relatedItem->getIdentifier();
        ?>
        <tr id="<?php echo $relatedItem->getRelationshipId(); ?>">
            <td><?php echo $relatedItem->getRelationshipName(); ?></td>
            <td><?php echo $relatedItemIdentifier; ?></td>
            <td><?php echo RelatedItemsEditor::getRelatedItemLink($relatedItemIdentifier) ?></td>
            <td>
                <button type="button" class="action-button edit-relationship-button"><?php echo __('Edit'); ?></button>
                <button type="button" class="action-button remove-relationship-button red button"><?php echo __('X'); ?></button>
            </td>
        </tr>
    <?php }; ?>
    <tr class="add-relationship-row">
        <td><?php echo get_view()->formSelect('relationship-type-code', null, array('multiple' => false), $formSelectRelationshipNames); ?></td>
        <td><?php echo get_view()->formText('related-item-identifier', null, array('size' => 5)); ?></td>
        <td></td>
        <td>
            <button type="button" class="action-button add-relationship-button"><?php echo __('Add'); ?></button>
            <button type="button" class="action-button edit-relationship-button"><?php echo __('Edit'); ?></button>
            <button type="button" class="action-button remove-relationship-button red button"><?php echo __('X'); ?></button>
        </td>
    </tr>
    </tbody>
</table>

<?php
echo '<div id="relationship-editor-speed-bar">SPEED BAR - choose Relationships or Items from the lists below</div>';
echo '<div id="relationship-editor-recents">';

// Emit an empty list of recent relationships. The client-side Javascript populates it.
echo '<div id="recent-relationships-section">';
echo '<div class="recent-relationships-title">' . __('Recent Relationships') . '</div>';
echo '<div id="recent-relationships"></div>';
echo '</div>'; // recent-relationships-section

// Emit a list of recently visited items.
echo '<div id="recent-items-section">';
echo '<div class="recent-items-title" xmlns="http://www.w3.org/1999/html">' . __('Recent Items<span>Click a title to edit that item\'s relationships') . '</span</div>';
echo '<div id="recent-items">';

$cookieValue = isset($_COOKIE['ITEMS']) ? $_COOKIE['ITEMS'] : '';
$recentItemIds = empty($cookieValue) ? array() : explode(',', $cookieValue);

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
    if ($recentIdentifier == $primaryItemIdentifier)
    {
        // Don't show the primary item in the list of recent items.
        continue;
    }
    $title = ItemMetadata::getItemTitle($recentItem);
    $url = html_escape(admin_url('avant/relationships/' . $recentItem->id));
    $type = ItemMetadata::getElementTextForElementName($recentItem, 'Type');
    $subject = ItemMetadata::getElementTextForElementName($recentItem, 'Subject');
    $metadata = "<span>Type:</span>$type<br/><span>Subject:</span>$subject";

    echo "<div class='recent-identifier' data-identifier='$recentIdentifier'>$recentIdentifier</div>";
    echo "<div class='recent-title'><a href='$url'>$title</a><div class='recent-metadata'>$metadata</div></div>";
}

echo '</div>'; // recent-items
echo '</div>'; // recent-items-section

echo '</div>'; // relationship-editor-recents
?>

<?php
    $relationshipNames = json_encode($formSelectRelationshipNames);
    echo get_view()->partial('/edit-relationships-script.php', array('primaryItemIdentifier' => $primaryItemIdentifier, 'relationshipNames' => $relationshipNames));
?>
