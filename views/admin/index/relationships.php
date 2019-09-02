<?php
$item = get_record_by_id('Item', $itemId);
if (empty($item))
{
    echo "No item found";
    return;
}
set_current_record('Item', $item);

$itemTitle = __('Relationships for Item %s', ItemMetadata::getItemIdentifier($item));
echo head(array('title' => $itemTitle, 'bodyclass'=>'relationships'));

$type = ItemMetadata::getElementTextForElementName($item, 'Type');
$subject = ItemMetadata::getElementTextForElementName($item, 'Subject');
$title = ItemMetadata::getItemTitle($item);

$html = '<div id="relationships-page-grid">';
$imageUrl = ItemPreview::getImageUrl($item, true, true);
$html .= "<img class='relationships-page-image' src='$imageUrl'>";

$html .= "<div class='relationships-page-metadata'>";
$html .= "<div class='relationships-page-title'>$title</div>";
$html .= "<div><span class='element-name'>Type:</span> $type</div>";
$html .= "<div><span class='element-name'>Subject:</span> $subject</div>";

$html .= "<div class='relationships-page-links'>";
$viewLink = html_escape(admin_url('items/show/' . metadata('item', 'id')));
$html .= "Admin: <a href='$viewLink'>" . __('View'). "</a>";
if (is_allowed($item, 'edit'))
{
    $editLink = link_to_item(__('Edit'), array(), 'edit');
    $html .= " | $editLink</span>";
}
$publicLink = html_escape(public_url('items/show/' . metadata('item', 'id')));
$html .= "<div><a href='$publicLink'>" . __('View Public Page'). "</a></div>";
$html .= "</div>";
$html .= "</div>";
$html .= "</div>";

echo $html;

$primaryItemIdentifier = ItemMetadata::getItemIdentifier($item);
?>
<table class="relationships-page-table">
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

    $relatedItemsModel = new RelatedItemsModel($item, $this);
    $relatedItems = $relatedItemsModel->getRelatedItems();

    $relatedItemsEditor = new RelatedItemsEditor($relatedItemsModel, $item);
    $formSelectRelationshipNames = $relatedItemsEditor->getRelationshipNamesSelectList();


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

<div id="recent-relationships">
    <?php
    $cookie = isset($_COOKIE['RELATIONSHIP']) ? $_COOKIE['RELATIONSHIP'] : '';
    $codes = explode(',', $cookie);
    foreach ($codes as $code)
    {
        echo "<div>$code</div>";
        ?>
    <?php }; ?>
</div>

<?php echo get_view()->partial('/edit-relationships-script.php', array('primaryItemIdentifier' => $primaryItemIdentifier)); ?>

<?php echo foot();?>
