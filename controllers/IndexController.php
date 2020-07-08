<?php

class AvantAdmin_IndexController extends Omeka_Controller_AbstractActionController
{
    public function dashboardAction()
    {
        return;
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
