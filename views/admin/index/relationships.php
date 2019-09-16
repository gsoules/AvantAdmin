<?php

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

$relatedItemsModel = new RelatedItemsModel($item, $this);
$relatedItemsEditor = new RelatedItemsEditor($relatedItemsModel, $item);

// Display the primary item's image and some metadata at the top of the page.
$relatedItemsEditor->emitPrimaryItem($item);

// Get the items that are already related to the primary item.
$relatedItems = $relatedItemsModel->getRelatedItems();

// Determine which relationships and related items are compatible with the primary item.
$allowedRelationshipSelections = $relatedItemsEditor->determineAllowedRelationshipSelections($item);
$selectedRelationshipCode = $relatedItemsEditor->determineSelectedRelationship();
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
        <td><?php echo get_view()->formSelect('relationship-type-code', $selectedRelationshipCode, array('multiple' => false), $allowedRelationshipSelections); ?></td>
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
// Generate the table contents html.
$html = $relatedItemsEditor->emitRecentlyViewedItems($relatedItems, $primaryItemIdentifier);

// Emit the header for the table of recent relationships and recent items.
echo '<div id="relationship-editor-busy"></div>';
echo '<div id="relationship-editor-choose-item">' . $relatedItemsEditor->getSelectedRelationshipTargetDescription() . '</div>';
echo '<div id="relationship-editor-recents">';
echo '<div id="recent-relationships-section">';
echo '<div class="recent-relationships-title">' . __('Recent Relationships') . '</div>';
echo '<div id="recent-relationships"></div>';
echo '</div>'; // recent-relationships-section

echo $html;
echo '</div>'; // relationship-editor-recents

// Emit the Javascript for supporting jQuery and Ajax.
$relationshipNames = json_encode($allowedRelationshipSelections);
echo $this->partial('/edit-relationships-script.php', array('primaryItemIdentifier' => $primaryItemIdentifier, 'relationshipNames' => $relationshipNames));
