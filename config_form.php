<?php $view = get_view(); ?>

<div class="field">
    <div class="two columns alpha">
        <label for="avantadmin_maintenance"><?php echo __('Maintenance'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('If checked, a "Down for maintenance" page will be displayed to public users. Logged in users will not be affected.'); ?></p>
        <?php echo $view->formCheckbox('avantadmin_maintenance', true, array('checked' => (boolean)get_option('avantadmin_maintenance'))); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantadmin_type_name"><?php echo __('Item Type Name'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Specify the exact name of the custom Item Type used by this installation.
         The custom item type provides elements, in addition to Dublin Core, that are unique to this installation."); ?></p>
        <?php echo $view->formText('avantadmin_type_name', get_option('avantadmin_type_name')); ?>
    </div>
</div>

