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

    public function ping()
    {
        $secondsInHour = 60 * 60;
        $secondsInDay = $secondsInHour * 24;
        $secondsInWeek = $secondsInDay * 7;
        $timeNow = time();
        $expiredTime = $timeNow - $secondsInHour;

        $db = get_db();
        $table = "{$db->prefix}sessions";

        $sql = "
                SELECT
                  id,
                  modified
                FROM
                  $table
                WHERE
                  modified < $expiredTime
            ";

        $results = $db->query($sql)->fetchAll();
        return count($results);

//        $date = new DateTime();
//        $date->getTimestamp();
//
//        $expiredCount = 0;
//
//        foreach ($results as $result)
//        {
//            $sessionSeconds = $timeNow - $result['modified'];
//            if ($sessionSeconds > $secondsInWeek)
//                $expiredCount += 1;
//        }
//
//        return $expiredCount;
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
        if (!plugin_is_active('AvantElasticsearch'))
        {
            $this->view->response = 'Remote requests are not supported by this installation';
            return;
        }

        if (AvantCommon::userIsSuper())
        {
            // This code is for development and testing. It allows a logged in super user to
            // simulate a remote request by putting the action and password on the query string.
            $siteId = isset($_GET['id']) ? $_GET['id'] : '';
            $action = isset($_GET['action']) ? $_GET['action'] : '';
            $password = isset($_GET['password']) ? $_GET['password'] : '';
        }
        else
        {
            $siteId = isset($_POST['id']) ? $_POST['id'] : '';
            $action = isset($_POST['action']) ? $_POST['action'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
        }

        switch ($action)
        {
            case 'ping':
                $expiredCount = $this->ping();
                $response = 'OK: ' . $expiredCount;
                break;

            case 'refresh-common':
                if (plugin_is_active('AvantVocabulary'))
                    $response = AvantVocabulary::refreshCommonVocabulary($siteId, $password);
                else
                    $response = 'AvantVocabulary is not activated';
                break;

            case 'rebuild-vocabularies':
                if (plugin_is_active('AvantVocabulary'))
                    $response = AvantVocabulary::rebuildCommonAndLocalVocabularies($siteId, $password);
                else
                    $response = 'AvantVocabulary is not activated';
                break;

            default:
                $response = 'Unsupported action: ' . $action;
                break;
        }

        $response = '[' . ElasticsearchConfig::getOptionValueForContributorId() . '] ' . $response;
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
