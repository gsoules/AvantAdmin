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

$itemTitle = __('Item %s', ItemMetadata::getItemIdentifier($item));

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
    <?php echo all_element_texts('item'); ?>
    <?php fire_plugin_hook('admin_items_show', array('item' => $item, 'view' => $this)); ?>
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
        <?php if (is_allowed($item, 'edit')): ?>
            <?php echo link_to_item(__('Edit Item'), array('class' => 'big green button'), 'edit'); ?>
            <a href="<?php echo html_escape(admin_url('avant/relationships/' . $item->id)); ?>" class="big green button"><?php echo __('Relationships'); ?></a>
        <?php endif; ?>
        <?php if (is_allowed($item, 'delete')): ?>
        <?php echo link_to_item(__('Delete This Item'), array('class' => 'delete-confirm big red button'), 'delete-confirm'); ?>
        <a href="<?php echo html_escape(public_url('items/show/' . metadata('item', 'id'))); ?>" class="big blue button"
           target="_blank"><?php echo __('View Public Page'); ?></a>
            <a href="<?php echo html_escape(admin_url('items/add/')); ?>" class="big blue button"
               target="_blank"><?php echo __('Add New Item'); ?></a>
        <?php endif; ?>
    </div>

    <?php
    echo get_specific_plugin_hook_output('AvantAdmin', 'admin_items_show_sidebar', array('view' => $this, 'item' => $item));
    echo get_specific_plugin_hook_output('Geolocation', 'public_items_show', array('view' => $this, 'item' => $item));
    echo get_specific_plugin_hook_output('AvantRelationships', 'show_relationships_visualization', array('view' => $this, 'item' => $item));
    echo get_specific_plugin_hook_output('AvantElements', 'admin_items_show_sidebar', array('view' => $this, 'item' => $item));
    ?>

    <?php if (metadata('item', 'has tags')): ?>
    <div class="tags panel">
        <h4><?php echo __('Tags'); ?></h4>
        <div id="tag-cloud">
            <?php echo common('tag-list', compact('item'), 'items'); ?>
        </div>
     </div>
    <?php endif; ?>

    <div class="file-metadata panel">
        <h4><?php echo __('File Metadata'); ?></h4>
        <div id="file-list">
            <?php if (!metadata('item', 'has files')):?>
                <p><?php echo __('There are no files for this item yet.');?> <?php echo link_to_item(__('Add a File'), array(), 'edit'); ?>.</p>
            <?php else: ?>
                <ul>
                    <?php foreach (loop('files', $this->item->Files) as $file): ?>
                        <li><?php echo link_to_file_show(array('class'=>'show', 'title'=>__('View File Metadata'))); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif;?>
        </div>
    </div>
</section>

<?php
echo foot();
echo $this->partial('recent-items-script.php', array('itemId' => $item->id));
?>
