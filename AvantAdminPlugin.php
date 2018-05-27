<?php

class AvantAdminPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head',
        'admin_items_panel_buttons',
        'admin_items_show_sidebar',
        'before_save_item',
        'config',
        'config_form',
        'define_routes',
        'initialize',
        'install'
    );

    protected $_filters = array(
        'admin_items_form_tabs',
        'admin_navigation_main',
        'public_show_admin_bar'
    );

    public function filterAdminItemsFormTabs($tabs, $args)
    {
        // Display a custom name for the "Item Type Metadata' tab on the admin/edit page.
        // If the administrator did not configure a name, use the default name.
        $newTabs = array();
        foreach ($tabs as $key => $tab) {
            if ($key == 'Item Type Metadata') {
                $tabName = get_option('avantadmin_type_name');
                if (!$tabName)
                    $tabName = $key;
            }
            else {
                $tabName = $key;
            }
            $newTabs[$tabName] = $tab;
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

    public function hookAdminHead($args)
    {
        queue_css_file('avantadmin');
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
        AvantAdmin::showItemHistory($args['item']);
    }

    public function hookBeforeSaveItem($args)
    {
        $item = $args['record'];
        AvantAdmin::setItemType($item);
    }

    public function hookConfig()
    {
        AvantAdmin::saveConfiguration();
    }

    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }

    public function hookDefineRoutes($args)
    {
        $args['router']->addConfig(new Zend_Config_Ini(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
    }

    public function hookInstall()
    {
        return;
    }

    public function hookInitialize()
    {
        // Register the dispatch filter controller plugin.
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new AvantAdmin_Controller_Plugin_DispatchFilter);
    }
}
