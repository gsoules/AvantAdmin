<?php
$view = get_view();

// If this page posted back with an error, get the invalid option value, otherwise get the option from the database.
$itemTypeName = isset($_POST[AdminConfig::OPTION_TYPE_NAME]) ? $_POST[AdminConfig::OPTION_TYPE_NAME] : get_option(AdminConfig::OPTION_TYPE_NAME);
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
        <label><?php echo CONFIG_LABEL_TYPE_NAME; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('The Item Type to be used for all items in this installation.'); ?></p>
        <?php echo $view->formText(AdminConfig::OPTION_TYPE_NAME, $itemTypeName, array('style' => 'width: 200px;')); ?>
    </div>
</div>

