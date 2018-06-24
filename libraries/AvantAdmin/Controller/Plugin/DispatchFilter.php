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
        $this->bypassOmekaSimpleSearch($isAdminRequest, $request, $moduleName, $controllerName, $actionName);
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
        $downForMaintenance = get_option(AdminConfig::OPTION_MAINTENANCE);
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

    protected function bypassOmekaSimpleSearch($isAdminRequest, $request, $moduleName, $controllerName, $actionName)
    {
        if (!plugin_is_active('AvantSearch'))
            return;

        if ($isAdminRequest && $controllerName == 'search' && $moduleName == 'default' && $actionName == 'index')
        {
            $parameters = $this->parseQueryString();
            if (isset($parameters['query_type']) && $parameters['query_type'] == 'keyword')
            {
                $query = $parameters['query'];
                $url = WEB_ROOT . "/find?query=$query";
                $this->getRedirector()->gotoUrl($url);
            }
        }
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

    protected function parseQueryString()
    {
        $queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $parameters = array();
        if (!empty($queryString))
        {
            // Get the value of the first parameter which is 'query'. For example in the following query string:
            // "query=map+seal+harbor&query_type=keyword&record_types%5B%5D=Item&submit_search=Search"
            // return just 'map+seal+harbor'.
            $parts = explode('&', $queryString);

            foreach ($parts as $part)
            {
                $pair  = explode('=', $part);
                $key = rawurldecode($pair[0]);
                $value = rawurldecode($pair[1]);
                $parameters[$key] = $value;
            }
        }
        return $parameters;
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
