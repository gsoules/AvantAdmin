<?php
$pageTitle = __('Recent Items');
echo head(array('bodyclass' => 'index primary-secondary', 'title' => $pageTitle));

echo AvantAdmin::emitRecentlyViewedItems(false);

echo foot();


