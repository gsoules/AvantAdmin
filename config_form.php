<?php
$view = get_view();

// If this page posted back with an error, get the invalid option value, otherwise get the option from the database.
$itemTypeName = isset($_POST['avantadmin_type_name']) ? $_POST['avantadmin_type_name'] : get_option('avantadmin_type_name');
?>

<div class="plugin-help">
    <a href="https://github.com/gsoules/AvantAdmin#usage" target="_blank">Learn about the configuration options on this page</a>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantadmin_maintenance"><?php echo __('Maintenance'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Display the "Down for maintenance" page.'); ?></p>
        <?php echo $view->formCheckbox('avantadmin_maintenance', true, array('checked' => (boolean)get_option('avantadmin_maintenance'))); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantadmin_type_name"><?php echo __('Item Type Name'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('The Item Type to be used for all items in this installation.'); ?></p>
        <?php echo $view->formText('avantadmin_type_name', $itemTypeName, array('style' => 'width: 200px;')); ?>
    </div>
</div>

