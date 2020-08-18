<?php

$primaryItem = get_record_by_id('Item', $itemId);
if (empty($primaryItem))
{
    echo "No item found";
    return;
}
set_current_record('Item', $primaryItem);

$primaryItemIdentifier = ItemMetadata::getItemIdentifier($primaryItem);
$itemTitle = __('Relationships Editor &ndash; Item %s', $primaryItemIdentifier);
echo head(array('title' => $itemTitle, 'bodyclass'=>'relationships'));

$relatedItemsModel = new RelatedItemsModel($primaryItem, $this);
$relatedItemsEditor = new RelatedItemsEditor($relatedItemsModel, $primaryItem);

// Display the primary item's image and some metadata at the top of the page.
$relatedItemsEditor->emitPrimaryItem($primaryItem);

// Get the items that are already related to the primary item.
$relatedItems = $relatedItemsModel->getRelatedItems();
$relatedItemsCount = count($relatedItems);

// Determine which relationships and related items are compatible with the primary item.
$allowedRelationshipSelections = $relatedItemsEditor->determineAllowedRelationshipSelections($primaryItem);
$selectedRelationshipCode = $relatedItemsEditor->determineSelectedRelationship();

$relatedItemsCountText = $relatedItemsCount ? $relatedItemsCount : "no";
$existingRelationshipMessage = __('Item %s has %s related item%s', $primaryItemIdentifier, $relatedItemsCountText, $relatedItemsCount == 1 ? '' : 's');

$addRelationshipMessage = $relatedItemsCount ? __('Add more relationships') : __('Add a relationship')
?>

<div id="relationships-editor-count"><?php echo $existingRelationshipMessage; ?></div>

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

<div id="relationships-editor-add-items"><?php echo $addRelationshipMessage; ?></div>

<?php
// Generate the table contents html. This method gets called here so that results can be used in the instructions.
$html = $relatedItemsEditor->emitRecentlyViewedItems($relatedItems, $primaryItem->id);

// Emit the header for the table of recent relationships and recent items.
echo '<div id="relationship-editor-busy"></div>';
echo $relatedItemsEditor->emitAddRelationshipInstructions();
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
