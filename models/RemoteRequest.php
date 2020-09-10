<?php

class RemoteRequest
{
    public function garbageCollection()
    {
        $secondsInDay = 60 * 60 * 24;
        $maxDays = $secondsInDay * 7;
        $timeNow = time();
        $expiredTime = $timeNow - $maxDays;

        $db = get_db();
        $table = "{$db->prefix}sessions";
        $sql = "DELETE FROM $table WHERE modified < $expiredTime";
        $results = $db->query($sql);
        $deletedSessions = $results->rowCount();

        $status = "$deletedSessions expired sessions deleted";
        return $status;
    }

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

        $response = '';
        $includeSiteIdInResponse = true;

        switch ($action)
        {
            case 'garbage-collection':
                $response = $this->garbageCollection();
                break;

            case 'ping':
                $response = 'OK';
                break;

            default:
                $index = strpos($action, '-');
                if ($index === false)
                    $response = 'Invalid action: ' . $action;
                else
                {
                    $actionPrefix = substr($action, 0, $index + 1);
                    switch ($actionPrefix)
                    {
                        case 'es-':
                            if (plugin_is_active('AvantElasticsearch'))
                                $response = AvantElasticsearch::handleRemoteRequest($action, $siteId, $password);
                            break;

                        case 'hybrid-':
                            if (plugin_is_active('AvantHybrid'))
                                $response = AvantHybrid::handleRemoteRequest($action, $siteId, $password);
                                $includeSiteIdInResponse = false;
                            break;

                        case 'vocab-':
                            if (plugin_is_active('AvantVocabulary'))
                                $response = AvantVocabulary::handleRemoteRequest($action, $siteId, $password);
                            break;

                        default:
                            $response = 'Unsupported action: ' . $action;
                    }
                }
        }

        if (empty($response))
            $response = 'Request denied';

        if ($includeSiteIdInResponse)
            $response = '[' . $siteId . '] ' . $response;

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