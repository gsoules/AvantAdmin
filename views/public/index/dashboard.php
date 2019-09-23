<?php
$pageTitle = __('Dashboard');
echo head(array('bodyclass' => 'index primary-secondary', 'title' => $pageTitle));

$localStats = '';
$sharedStats = '';

$useElasticsearch = AvantSearch::useElasticsearch();
if ($useElasticsearch)
{
    $sharedIndexName = AvantElasticsearch::getNameOfLocalIndex();
    $localStats = AvantElasticsearch::generateContributorStatistics($sharedIndexName);
    $localIndexName = AvantElasticsearch::getNameOfSharedIndex();
    $sharedStats = AvantElasticsearch::generateContributorStatistics($localIndexName);
}

$html = '<div class="dashboard-message">';

$html .= '<h1>' . __('Welcome to the Digital Archive') . '</h1>';

$user = current_user();

if ($user)
{
    $html .= '<p>' . __('You are logged in as <strong>%s</strong>', $user->username) . '</p>';
}

$html .= '<p>' . __('The following organizations have made their collections searchable from this site:') . '</p>';

if (!empty($sharedStats))
{
    $html .= $sharedStats;
}

if ($user && !empty($localStats))
{
    $html .= '<br/>';
    $html .= '<p>' . __('Statistics for this site (including non-public items) appear below:') . '</p>';
    $html .= $localStats;
}

if (!$user)
{
    $html .= '<p><a href="' . WEB_ROOT . '/users/login">Administrator Login</a></p>';
}
else
{
    $html .= '<p><a href="' . WEB_ROOT . '/users/logout">Logout</a></p>';
}

$html .= '</div>';
echo $html;
echo foot();


