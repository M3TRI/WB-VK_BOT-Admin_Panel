<?php
function searchUsers($users, $search_in) {
    $results = [];

    foreach ($users as $user) {
        $match = true;

        foreach ($search_in as $key => $value) {
            switch ($key) {
                case 'subscriptionId':
                    if ($user->subscriptionId != $value) {
                        $match = false;
                    }
                    break;
                case 'userId':
                    if ($user->userId != $value) {
                        $match = false;
                    }
                    break;
                case 'botName':
                    if (stripos($user->botName, $value) === false) {
                        $match = false;
                    }
                    break;
                case 'userChannel':
                    if (stripos($user->userChannel, $value) === false) {
                        $match = false;
                    }
                    break;
                case 'subscriptionEndDate':
                    if ($user->subscriptionEndDate != $value) {
                        $match = false;
                    }
                    break;
                default:
                    $match = false;
                    break;
            }
            if (!$match) {
                break;
            }
        }
        if ($match) {
            $results[] = $user;
        }
    }

    return $results;
}