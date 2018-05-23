<?php

class AvantAdmin_Controller_Plugin_DispatchFilter extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $isAdminRequest = $request->getParam('admin', false);
        $moduleName = $request->getModuleName();
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();

        $this->preventAdminAccess();
        $this->bypassOmekaDashboard($isAdminRequest, $moduleName, $controllerName, $actionName);
        $this->bypassOmekaAdminItemsShow($isAdminRequest, $request, $controllerName, $actionName);
    }

    protected function bypassOmekaAdminItemsShow($isAdminRequest, $request, $controllerName, $actionName)
    {
        if (!$isAdminRequest || $controllerName != 'items')
        {
            // Only handle admin requests for items.
            return;
        }

        if ($actionName == 'show')
        {
            // Display the AvantAdmin Show page instead of the Omeka Show page.
            $id = $request->getParam('id');
            $url = WEB_ROOT . '/admin/avant/show/' . $id;
            $this->getRedirector()->gotoUrl($url);
        }
        elseif ($actionName == 'browse')
        {
            // Determine if Omeka is redirecting to the Browse page after the user has added a new item.
            // If so, display the new item's Show page instead since that's what the user usually wants to see.
            // Assume this is the case if the most recent item's added and modified dates are the same.
            $mostRecentItem = get_recent_items(1)[0];
            $isNewItem = $mostRecentItem->added == $mostRecentItem->modified;

            if ($isNewItem)
            {
                // Touch the new item's modified date so that this logic only executes immediately after the Add.
                $db = get_db();
                $db->query("UPDATE `{$db->Items}` SET modified = NOW() WHERE id = {$mostRecentItem->id}");

                // Display the AvantAdmin Show page for the new item.
                $url = WEB_ROOT . '/admin/avant/show/' . $mostRecentItem->id;
                $this->getRedirector()->gotoUrl($url);
            }
        }
    }

    protected function bypassOmekaDashboard($isAdminRequest, $moduleName, $controllerName, $actionName)
    {
        $downForMaintenance = get_option('avantadmin_maintenance');
        if ($downForMaintenance)
        {
            $noCurrentUser = empty(current_user());
            if ($noCurrentUser && $actionName != 'login' && $actionName != 'maintenance')
            {
                $url = WEB_ROOT . '/avant/maintenance';
                $this->getRedirector()->gotoUrl($url);
                return;
            }
        }

        $isDashboardRequest = $moduleName == 'default' && $controllerName == 'index' && $actionName == 'index';

        if ($isDashboardRequest)
            $this->goToDashboardPage($isAdminRequest);
    }

    protected function getRedirector()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    }

    protected function goToDashboardPage($admin = false)
    {
        $url = WEB_ROOT . ($admin ? '/admin' : '') . '/avant/dashboard';
        $this->getRedirector()->gotoUrl($url);
    }

    protected function preventAdminAccess()
    {
        // Prevent users with the researcher role from accessing the admin pages.
        $user = current_user();
        if ($user && $user->role == 'researcher' && is_admin_theme()) {
            $this->goToDashboardPage();
        }
    }
}
