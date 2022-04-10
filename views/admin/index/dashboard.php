<?php
$pageTitle = __('Avant Dashboard');
echo head(array('bodyclass'=>'index primary-secondary', 'title'=>$pageTitle));

$total_items = total_records('Item');
$total_tags = total_records('Tag');
$stats = array(
    array(link_to('items', null, $total_items), __(plural('item', 'items', $total_items))),
    array(link_to('tags', null, $total_tags), __(plural('tag', 'tags', $total_tags)))
); ?>
<?php $stats = apply_filters('admin_dashboard_stats', $stats, array('view' => $this)); ?>

<?php // Retrieve the latest version of Omeka by pinging the Omeka server. ?>
<?php $userRole = current_user()->role; ?>
<?php if ($userRole == 'super'): ?>
    <?php $latestVersion = latest_omeka_version(); ?>
    <?php if ($latestVersion and version_compare(OMEKA_VERSION, $latestVersion, '<')): ?>
        <div id="flash">
            <ul>
                <li class="success"><?php echo __('A new version of Omeka is available for download.'); ?>
                    <a href="http://omeka.org/download/"><?php echo __('Upgrade to %s', $latestVersion); ?></a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>

<section id="stats">
    <?php foreach ($stats as $statInfo): ?>
        <p><span class="number"><?php echo $statInfo[0]; ?></span><br><?php echo $statInfo[1]; ?></p>
    <?php endforeach; ?>
</section>

<?php $panels = array(); ?>

<?php ob_start(); ?>
<h2><?php echo __('Recently Added Items'); ?></h2>
<?php if (is_allowed('Items', 'add')): ?>
    <div class="add-new-link"><p><a class="add-new-item" href="<?php echo html_escape(url('items/add')); ?>"><?php echo __('Add a new item'); ?></a></p></div>
<?php endif; ?>
<?php
$addedItems = get_db()->getTable('Item')->findBy(array('sort_field' => 'added', 'sort_dir' => 'd'), 100);
set_loop_records('items', $addedItems);
foreach (loop('items') as $item):
    $userName = ItemHistory::getUserName($item->owner_id);
    $userName = empty($userName) ? '' : " <i>$userName</i>";
    $identifier = ItemMetadata::getItemIdentifier($item);
    $title = ItemMetadata::getItemTitle($item);
    $dateAdded = ItemHistory::formatHistoryDate($item->added);
    $viewLink = html_escape(admin_url('items/show/' . metadata('item', 'id')));
    ?>
    <div class="recent-row">
        <p class="recent"><?php echo "<a href='$viewLink' target='_blank'>$title</a> ($identifier, $userName, $dateAdded)"; ?></p>
        <?php if (is_allowed($item, 'edit')): ?>
            <p class="dash-edit"><?php echo link_to_item(__('Edit'), array('target' => '_blank'), 'edit'); ?></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php $panels[] = ob_get_clean(); ?>

<?php ob_start(); ?>
<h2><?php echo __('Recently Modified Items'); ?></h2>
<?php
$modifiedItems = get_db()->getTable('Item')->findBy(array('sort_field' => 'modified', 'sort_dir' => 'd'), 100);
set_loop_records('items', $modifiedItems);
foreach (loop('items') as $item):
    $userName = ItemHistory::getMostRecentUserName($item->id);
    $userName = empty($userName) ? '' : " <i>$userName</i>";
    $identifier = ItemMetadata::getItemIdentifier($item);
    $title = ItemMetadata::getItemTitle($item);
    $modified = $item->modified;
    $dateModified = ItemHistory::formatHistoryDate($modified);
    $viewLink = html_escape(admin_url('items/show/' . metadata('item', 'id')));
    ?>
    <div class="recent-row">
        <p class="recent"><?php echo "<a href='$viewLink' target='_blank'>$title</a> ($identifier, $userName, $dateModified)"; ?></p>
        <?php if (is_allowed($item, 'edit')): ?>
            <p class="dash-edit"><?php echo link_to_item(__('Edit'), array('target' => '_blank'), 'edit'); ?></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php $panels[] = ob_get_clean(); ?>

<?php $panels = apply_filters('admin_dashboard_panels', $panels, array('view' => $this)); ?>
<?php for ($i = 0; $i < count($panels); $i++): ?>
    <section style="width:48%;" class="five columns <?php echo ($i & 1) ? 'omega' : 'alpha'; ?>">
        <div class="panel">
            <?php echo $panels[$i]; ?>
        </div>
    </section>
<?php endfor; ?>

<?php fire_plugin_hook('admin_dashboard', array('view' => $this)); ?>

<?php echo foot(); ?>
