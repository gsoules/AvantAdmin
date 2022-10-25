<?php

class AvantAdmin_IndexController extends Omeka_Controller_AbstractActionController
{
    public function dashboardAction()
    {
        return;
    }

    public function itemAction()
    {
        // This method provides a way to show an item by specifying its Identifier instead
        // of its Omeka item Id. This is usefull when the caller does not know the item Id.
        $identifier = $this->getParam('identifier');
        $id = ItemMetadata::getItemIdFromIdentifier($identifier);
        if ($id)
            AvantSearch::redirectToShowPageForItem($id);
        else
            $this->showAction();
    }

    public function maintenanceAction()
    {
        return;
    }

    public function recentItemsAction()
    {
        return;
    }

    public function relationshipsAction()
    {
        $id = $this->getParam('item-id');
        $this->view->itemId = $id;
    }

    public function remoteAction()
    {
        $remoteRequest = new RemoteRequest();
        $this->view->response = $remoteRequest->handleRemoteRequest();
    }

    public function showAction()
    {
        $id = $this->getParam('item-id');
        $this->view->itemId = $id;
    }
}
