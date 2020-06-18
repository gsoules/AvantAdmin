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
        $response = 'Invalid remote request';

        if (isset($_POST['action']))
        {
            $action = $_POST['action'];

            switch ($action)
            {
                case 'refresh-common':
                    if (plugin_is_active('AvantVocabulary'))
                        $response = AvantVocabulary::refreshCommonVocabulary();
                    else
                        $response = 'AvantVocabulary is not activated';
                    break;

                default:
                    $response = 'Unsupported action: ' . $action;
                    break;
            }
        }

        $this->view->response = $response;
    }

    public function showAction()
    {
        $id = $this->getParam('item-id');
        $this->view->itemId = $id;
    }

    // This method is here in case a future remote action requires authentication of a user name and password.
    // It has not been fully tested, but seems to work and is at a minimum a good starting point for this logic.
    private function userIsAuthentic($user, $password)
    {
        $authAdapter = new Omeka_Auth_Adapter_UserTable(get_db());
        $authAdapter
            ->setIdentity($user)
            ->setCredential($password);

        $auth = Zend_Auth::getInstance();

        $authResult = $auth->authenticate($authAdapter);
        if ($authResult->isValid())
        {
            Zend_Session::forgetMe();
            return true;
        }
        return false;
    }
}
