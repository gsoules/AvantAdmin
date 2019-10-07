<?php
$pageTitle = __('Recent Items');
echo head(array('bodyclass' => 'index primary-secondary', 'title' => $pageTitle));
$recentlyViewedItems = AvantAdmin::getRecentlyViewedItems();

echo AvantAdmin::emitRecentlyViewedItems($recentlyViewedItems);

echo foot();


