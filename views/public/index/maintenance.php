<?php
$downForMaintenance = get_option(AdminConfig::OPTION_MAINTENANCE);
if (!$downForMaintenance)
{
    // If the user refreshes this maintenance page after the site has come back online, take them to the dashboard.
    $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    $redirector->gotoUrl(WEB_ROOT . '/admin/dashboard');
}

$pageTitle = __('Maintenance');
echo head(array('title' => $pageTitle));

$html = "<h3>This site is temporarily down for maintenance</h3>";
$html .= "<p>We apologize for the inconvenience. Please come back in a little while.</p>";
echo $html;
echo foot();

?>
