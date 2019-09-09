<?php
$pageTitle = __('Dashboard');
echo head(array('bodyclass' => 'index primary-secondary', 'title' => $pageTitle));

$stats = '';
$useElasticsearch = AvantSearch::useElasticsearch();
if ($useElasticsearch)
{
    $indexName = AvantElasticsearch::getNameOfLocalIndex();
    $stats = AvantElasticsearch::generateContributorStatistics($indexName);
}

$html = '<div class="dashboard-message">';

$user = current_user();
if ($user)
{
    $html .= '<h1>' . __('Welcome to the Digital Archive') . '</h1>';
    $html .= '<p>' . __('You are logged in as <strong>%s</strong>', $user->username) . ' &nbsp;&nbsp; <a href="' . WEB_ROOT . '/users/logout">Logout</a></p>';
    $html .= '<p>' . __('Statistics for this site are below. To see statistics for all organizations that you can search from this site, click ') . ' <a href="' . public_url('/find/contributors') . '">here.</a></p>';
}
else
{
    $html .= '<p><a href="' . WEB_ROOT . '/users/login">Administrator Login</a></p>';
}

if (empty($stats))
{
    $html .= '</div>';
}
else
{
    $html .= '</div>';
    $html .= $stats;
}

echo $html;
echo foot();


