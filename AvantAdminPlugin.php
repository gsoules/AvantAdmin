<?php

class AvantAdminPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head',
        'admin_items_panel_buttons',
        'admin_items_show_sidebar',
        'after_save_item',
        'before_save_item',
        'config',
        'config_form',
        'define_routes',
        'initialize',
        'install',
        'public_head',
        'upgrade'
    );

    protected $_filters = array(
        'admin_items_form_tabs',
        'admin_navigation_main',
        'public_navigation_admin_bar',
        'public_show_admin_bar'
    );

    public function filterAdminItemsFormTabs($tabs, $args)
    {
        // Get this item's image(s).
        $images = item_image_gallery(array('linkWrapper' => array('class' => 'admin-thumb panel'), 'link' => array('target' => '_blank')), 'thumbnail', false);

        // Merge the contents of the first two tabs into a single tab that shows the item's image(s) at the top.
        $fieldsSeparator = '<div class="field field-separator">' . AdminConfig::getItemTypeName() . ' ' . __('Fields') . '</div>';
        $newTabs = array();
        $newTabs[__('Fields')] = $images . $tabs['Dublin Core'] . $fieldsSeparator . $tabs['Item Type Metadata'];

        // Append the remaining tabs.
        foreach ($tabs as $key => $tab)
        {
            if ($key == 'Dublin Core' || $key == 'Item Type Metadata')
                continue;
            $newTabs[$key] = $tab;
        }
        return $newTabs;
    }

    public function filterAdminNavigationMain($nav)
    {
        // Remove 'Collections' from the admin left menu panel.
        $key = array_search('Collections', array_column($nav, 'label'));
        if ($key)
            unset($nav[$key]);

        return $nav;
    }

    public function filterPublicNavigationAdminBar($links)
    {
        $newLinks[] = array(
            'label' => __('Add item'),
            'uri' => admin_url('/items/add/')
        );

        foreach ($links as $link)
            $newLinks[] = $link;

        return $newLinks;
    }

    public function filterPublicShowAdminBar($show)
    {
        // Don't show the admin bar unless a user is logged in and they are not a researcher.
        $user = current_user();

        if (empty($user))
            return false;

        if ($user->role == 'researcher')
            return false;

        return true;
    }

    protected function head()
    {
        queue_css_file('avantadmin-recent');
        queue_js_file('recent-items-script');
    }

    public function hookAfterSaveItem($args)
    {
        if (!AvantCommon::userClickedSaveChanges())
        {
            // Don't log a save that is done programmatically such as when batch editing.
            return;
        }

        ItemHistory::logItemSave($args['record']);
    }

    public function hookAdminHead($args)
    {
        queue_css_file('avantadmin');
        $this->head();
    }

    public function hookAdminItemsPanelButtons($args)
    {
        // Add a 'Cancel' button on the admin right button panel. It appears when editing an existing
        // item or adding a new item. When editing, pressing the Cancel button takes the user back to
        // the Show page for the item. When adding a new item, it takes them to the Dashboard.
        $itemId = $args['record']->id;
        $url = $itemId ? 'items/show/' . $itemId : '.';
        echo '<a href=' . html_escape(admin_url($url)) . ' class="big blue button">' . __('Cancel') . '</a>';
    }

    public function hookAdminItemsShowSidebar($args)
    {
        AvantAdmin::showPublicPrivateStatus($args['item']);
        ItemHistory::showItemHistory($args['item']);
    }

    public function hookBeforeSaveItem($args)
    {
        $item = $args['record'];
        AvantAdmin::setItemType($item);
    }

    public function hookConfig()
    {
        AdminConfig::saveConfiguration();
    }

    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }

    public function hookDefineRoutes($args)
    {
        $args['router']->addConfig(new Zend_Config_Ini(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
    }

    public function hookInitialize()
    {
        // Register the dispatch filter controller plugin.
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new AvantAdmin_Controller_Plugin_DispatchFilter);
    }

    public function hookInstall()
    {
        ItemHistory::createAdminLogTable();
    }

    public function hookPublicHead()
    {
        AvantAdmin::emitDynamicCss();
        $this->head();
    }

    public function hookUpgrade($args)
    {
        if (version_compare($args['old_version'], '2.0.0', '<='))
        {
            ItemHistory::createAdminLogTable();
        }
    }
}
