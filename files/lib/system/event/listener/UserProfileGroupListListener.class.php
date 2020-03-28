<?php
namespace wcf\system\event\listener;

use wcf\data\user\group\UserGroup;
use wcf\page\UserPage;
use wcf\system\WCF;

/**
 * Provides the list of assigned user groups in user profiles.
 *
 * @author      Niklas Friedrich Gerstner
 * @copyright   2020 Krymo Software
 * @license     Krymo Software - Free Products License <https://krymo.software/license-terms/#free-products>
 * @package     WoltLabSuite\Core\System\Event\Listener
 */
class UserProfileGroupListListener implements IParameterizedEventListener {
    /**
     * instance of UserPage
     * @var	UserPage
     */
    protected $eventObj;

    /**
     * list of user groups which are assigned to the user
     * @var UserGroup[]
     */
    protected $userGroups = [];

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters) {
        if (!PROFILE_ENABLE_GROUPLIST) {
            return;
        }

        $this->eventObj = $eventObj;
        $this->$eventName();
    }

    /**
     * Handles the assignVariables event.
     */
    protected function assignVariables() {
        WCF::getTPL()->assign([
            'userGroups' => $this->userGroups
        ]);
    }

    /**
     * Handles the readData event.
     */
    protected function readData() {
        $this->userGroups = UserGroup::getGroupsByIDs($this->eventObj->user->getGroupIDs());

        if (!PROFILE_GROUPLIST_HIDDEN_GROUPS) {
            return;
        }

        $hiddenGroupIDs = [];

        // removes all whitespaces
        $hiddenGroups = preg_replace('/\s+/', '', PROFILE_GROUPLIST_HIDDEN_GROUPS);

        if ($groupIDs = explode(",", $hiddenGroups)) {
            foreach ($groupIDs as $groupID) {
                if (UserGroup::getGroupByID($groupID)) {
                    $hiddenGroupIDs[] = $groupID;
                }
            }
        }

        foreach ($this->userGroups as $userGroup) {
            if (in_array($userGroup->groupID, $hiddenGroupIDs)) {
                unset($this->userGroups[$userGroup->groupID]);
            }
        }
    }
}