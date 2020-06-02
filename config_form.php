<?php
$view = get_view();

$itemTypeName = AdminConfig::getItemTypeName();

if (ConfigOptions::configurationErrorsDetected())
{
    // The page posted back with an error because the item type name is invalid. Redisplay the bad name.
    $itemTypeName = $_POST[AdminConfig::OPTION_ITEM_TYPE];
}
?>

<div class="plugin-help">
    <a href="https://github.com/gsoules/AvantAdmin#usage" target="_blank">Learn about the configuration options on this page</a>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_MAINTENANCE; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Display the "Down for maintenance" page.'); ?></p>
        <?php echo $view->formCheckbox(AdminConfig::OPTION_MAINTENANCE, true, array('checked' => (boolean)get_option(AdminConfig::OPTION_MAINTENANCE))); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_ITEM_TYPE; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('The Item Type to be used for all items in this installation.'); ?></p>
        <?php echo $view->formText(AdminConfig::OPTION_ITEM_TYPE, $itemTypeName, array('style' => 'width: 200px;')); ?>
    </div>
</div>

