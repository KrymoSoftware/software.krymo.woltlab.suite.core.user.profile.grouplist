<?php
namespace wcf\system\event\listener;

use wcf\data\user\group\UserGroup;
use wcf\page\UserPage;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Provides the list of assigned user groups in user profiles.
 *
 * @author      Niklas Friedrich Gerstner
 * @copyright   2020 Krymo Software
 * @license     Krymo Software - Free Products License <https://krymo.software/license-terms/#free-products>
 * @package     WoltLabSuite\Core\System\Event\Listener
 */
class UserPageGroupListListener implements IParameterizedEventListener {
    /**
     * instance of UserPage
     * @var	UserPage
     */
    protected $eventObj;

    /**
     * true if the user can view the group list
     * @var boolean
     */
    protected $canViewUserPageGroupList = false;

    /**
     * list of user groups which are assigned to the user
     * @var UserGroup[]
     */
    protected $userGroups = [];

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters) {
        $this->eventObj = $eventObj;
        $this->$eventName();
    }

    /**
     * Handles the assignVariables event.
     */
    protected function assignVariables() {
        WCF::getTPL()->assign([
            'canViewUserPageGroupList' => $this->canViewUserPageGroupList,
            'userGroups' => $this->userGroups
        ]);
    }

    /**
     * Handles the readData event.
     */
    protected function readData() {
        $this->canViewUserPageGroupList = WCF::getSession()->getPermission('user.profile.canViewUserPageGroupList');

        if (!$this->canViewUserPageGroupList) {
            $isOwnProfile = $this->eventObj->user->userID == WCF::getUser()->userID;
            $this->canViewUserPageGroupList = $isOwnProfile && WCF::getSession()->getPermission('user.profile.canViewUserPageGroupListOwnProfile');
        }

        if ($this->canViewUserPageGroupList) {
            $this->userGroups = UserGroup::getGroupsByIDs(array_diff($this->eventObj->user->getGroupIDs(),  ArrayUtil::toIntegerArray(ArrayUtil::trim(explode(',', PROFILE_GROUPLIST_HIDDEN_GROUPS)))));
        }
    }
}