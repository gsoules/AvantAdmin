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

    public function relationshipsAction()
    {
        $id = $this->getParam('item-id');
        $this->view->itemId = $id;
    }

    public function showAction()
    {
        $id = $this->getParam('item-id');
        $this->view->itemId = $id;
    }
}
