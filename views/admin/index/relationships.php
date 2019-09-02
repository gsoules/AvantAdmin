<?php
$item = get_record_by_id('Item', $itemId);
if (empty($item))
{
    echo "No item found";
    return;
}
set_current_record('Item', $item);

$itemTitle = metadata('item', 'display_title');
if ($itemTitle != '' && $itemTitle != __('[Untitled]')) {
    $itemTitle = ': &quot;' . $itemTitle . '&quot; ';
} else {
    $itemTitle = '';
}

$itemTitle = __('Relationships for Item %s', ItemMetadata::getItemIdentifier($item));

echo head(array('title' => $itemTitle, 'bodyclass'=>'items show'));
echo flash();
?>

<section class="seven columns alpha">
    <?php echo flash(); ?>
    <?php
    echo item_image_gallery(
        array('linkWrapper' => array('class' => 'admin-thumb panel'),
            'link' => array('target' => '_blank')), 'thumbnail', false);
    ?>

    <?php
    $title = ItemMetadata::getItemTitle($item);
    $type = ItemMetadata::getElementTextForElementName($item, 'Type');
    $subject = ItemMetadata::getElementTextForElementName($item, 'Subject');
    echo $title;
    echo $type;
    echo $subject;

    $itemPreview = new ItemPreview($item);
    echo $itemPreview->emitItemPreview();
    ?>
</section>

<section class="three columns omega">
    <ul class="pagination">
        <?php if (($prevLink = link_to_previous_item_show(__('Prev Item')))): ?>
        <li id="previous-item" class="previous">
            <?php echo $prevLink; ?>
        </li>
        <?php endif; ?>
        <?php if (($nextLink = link_to_next_item_show(__('Next Item')))): ?>
        <li id="next-item" class="next">
            <?php echo $nextLink; ?>
        </li>
        <?php endif; ?>
    </ul>

    <div id="edit" class="panel">
<!--        --><?php //if (is_allowed($item, 'edit')): ?>
<!--        --><?php
//        echo link_to_item(__('Edit'), array('class'=>'big green button'), 'edit'); ?>
<!--        --><?php //endif; ?>
        <a href="<?php echo html_escape(public_url('items/show/'.metadata('item', 'id'))); ?>" class="big blue button" target="_blank"><?php echo __('View Public Page'); ?></a>
<!--        --><?php //if (is_allowed($item, 'delete')): ?>
<!--        --><?php //echo link_to_item(__('Delete This Item'), array('class' => 'delete-confirm big red button'), 'delete-confirm'); ?>
<!--        <a href="--><?php //echo html_escape(admin_url('items/add/')); ?><!--" class="big blue button" target="_blank">--><?php //echo __('Add New Item'); ?><!--</a>-->
<!--        --><?php //endif; ?>
    </div>

    <?php
    echo get_specific_plugin_hook_output('AvantRelationships', 'show_relationships_visualization', array('view' => $this, 'item' => $item));
    ?>
</section>

<?php
$primaryItemIdentifier = ItemMetadata::getItemIdentifier($item);
?>
<div><?php echo ItemMetadata::getItemTitle($item); ?></div>
<br/>
<div>This item is: <?php echo $primaryItemIdentifier; ?></div>
<br/>
<table>
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
