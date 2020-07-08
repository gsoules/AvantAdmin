<?php

class RemoteRequest
{
    public function handleRemoteRequest()
    {
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
            case 'health-check':
                $response = $this->healthCheck($siteId, $password);
                break;

            case 'ping':
                $response = 'OK: ';
                break;

            default:
                if (substr($action, 0, 6) == 'vocab-' && plugin_is_active('AvantVocabulary'))
                    $response = AvantVocabulary::handleRemoteRequest($action, $siteId, $password);
                else
                    $response = 'Unsupported action: ' . $action;
                break;
        }

        return '[' . $siteId . '] ' . $response;
    }

    public function healthCheck($siteId, $password)
    {
        $secondsInDay = 60 * 60 * 24;
        $maxDays = $secondsInDay * 14;
        $timeNow = time();
        $expiredTime = $timeNow - $maxDays;

        $db = get_db();
        $table = "{$db->prefix}sessions";

        $sql = "DELETE FROM $table WHERE modified < $expiredTime";

        $results = $db->query($sql);
        $deletedSessions = $results->rowCount();
        $response = "$deletedSessions expired sessions deleted";

        if (plugin_is_active('AvantElasticsearch') && AvantElasticsearch::remoteRequestIsValid($siteId, $password))
        {
            $response .= "\nOK";
        }

        return $response;
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