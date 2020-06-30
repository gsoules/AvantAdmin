<?php
$pageTitle = __('Dashboard');
echo head(array('bodyclass' => 'index primary-secondary', 'title' => $pageTitle));

$localStats = '';
$sharedStats = '';

$useElasticsearch = plugin_is_active('AvantSearch') && AvantSearch::useElasticsearch();
if ($useElasticsearch)
{
    $localIndexIsEnabled = (bool)get_option(ElasticsearchConfig::OPTION_ES_LOCAL) == true;
    if ($localIndexIsEnabled)
    {
        $localIndexName = AvantElasticsearch::getNameOfLocalIndex();
        $localStats = AvantElasticsearch::generateContributorStatistics($localIndexName);
    }

    $sharedIndexIsEnabled = (bool)get_option(ElasticsearchConfig::OPTION_ES_SHARE) == true;
    if ($sharedIndexIsEnabled)
    {
        $sharedIndexName = AvantElasticsearch::getNameOfSharedIndex();
        $sharedStats = AvantElasticsearch::generateContributorStatistics($sharedIndexName);
    }
}

$html = '<div class="dashboard-message">';

$html .= '<h1>' . __('Contributing Organizations') . '</h1>';
$html .= '<p>' . __('The following organizations have made their collections searchable from this site:') . '</p>';

if (!empty($sharedStats))
{
    $html .= $sharedStats;
}

$user = current_user();
if ($user && !empty($localStats))
{
    $html .= '<br/>';
    $html .= '<p>' . __('Statistics for this site (including non-public items) appear below:') . '</p>';
    $html .= $localStats;
}

$html .= '</div>';
echo $html;
echo foot();


