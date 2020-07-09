<?php
$pageTitle = __('Recent Items');
echo head(array('bodyclass' => 'index primary-secondary', 'title' => $pageTitle));
$recentlyViewedItems = AvantCommon::getRecentlyViewedItems();

echo AvantCommon::emitRecentlyViewedItems($recentlyViewedItems);

echo foot();


