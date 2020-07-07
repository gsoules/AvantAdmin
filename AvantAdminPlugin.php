<?php

class AvantAdminPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head',
        'admin_items_panel_buttons',
        'admin_items_show_sidebar',
        'after_delete_item',
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

    public static function allowAddItem()
    {
        $allowAddItem = true;
        if (plugin_is_active('AvantElasticsearch'))
        {
            $allowAddItem = (bool)get_option(ElasticsearchConfig::OPTION_ES_LOCAL) == true;
        }
        return $allowAddItem;
    }

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
        $newNav = array();

        // Remove the Collection nav item because the Digital Archive does not use Omeka collections.
        $exclusionList[] = 'Collections';

        // Remove dangerous menu items from non-super users.
        if (!AvantCommon::userIsSuper())
            $exclusionList = array_merge($exclusionList, array('Items', 'Item Types', 'Bulk Editor'));

        foreach ($nav as $navEntry)
        {
            if (in_array($navEntry['label'], $exclusionList))
                continue;
            $newNav[] = $navEntry;
        }

        return $newNav;
    }

    public function filterPublicNavigationAdminBar($links)
    {
        if (self::allowAddItem())
        {
            $newLinks[] = array(
                'label' => __('Add item'),
                'uri' => admin_url('/items/add/')
            );
        }

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

    public function hookAfterDeleteItem($args)
    {
        ItemHistory::logItemDelete($args['record']);
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

        if (plugin_is_active('AvantElasticsearch'))
        {
            queue_css_file('avantadmin-disable-batch-edit');

            if (!self::allowAddItem())
            {
                // Hide the Add Item button on the Add an Item page to prevent items from being added to a site
                // that only displays shared content. The user can still get to the page, but they can't save it.
                echo PHP_EOL . '<style>';
                echo "#add_item{display:none;}";
                echo '</style>'. PHP_EOL;
            }
        }

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
