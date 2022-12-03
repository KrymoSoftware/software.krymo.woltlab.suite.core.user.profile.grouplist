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
 * @copyright   2022 Krymo Software
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
        $user = $this->eventObj->user;
        $this->canViewUserPageGroupList = WCF::getSession()->getPermission('user.profile.canViewUserPageGroupList');

        if (!$this->canViewUserPageGroupList) {
            $isOwnProfile = $user->userID === WCF::getUser()->userID;
            $this->canViewUserPageGroupList = $isOwnProfile && WCF::getSession()->getPermission('user.profile.canViewUserPageGroupListOwnProfile');
        }

        if ($this->canViewUserPageGroupList) {
            $hiddenGroupIDs = ArrayUtil::toIntegerArray(ArrayUtil::trim(explode(',', PROFILE_GROUPLIST_HIDDEN_GROUPS)));
            $shownGroupIDs = array_diff($user->getGroupIDs(), $hiddenGroupIDs);
            $userGroups = UserGroup::getGroupsByIDs($shownGroupIDs);

            switch(PROFILE_GROUPLIST_SORT_BY) {
                case 'priority_desc': {
                    \uasort($userGroups, static function (UserGroup $groupA, UserGroup $groupB) {
                        return static::compareGroupPriority($groupA, $groupB);
                    });
                    break;
                }
                case 'priority_asc': {
                    \uasort($userGroups, static function (UserGroup $groupA, UserGroup $groupB) {
                        return static::compareGroupPriority($groupA, $groupB, false);
                    });
                    break;
                }
                case 'alphabetical': {
                    UserGroup::sortGroups($userGroups);
                    break;
                }
            }

            $this->userGroups = $userGroups;
        }
    }

    private static function compareGroupPriority(UserGroup $groupA, UserGroup $groupB, bool $sortDescending = true): int {
        if ($groupA->priority === $groupB->priority) {
            return 0;
        }

        $value = ($groupA->priority < $groupB->priority) ? 1 : -1;

        if (!$sortDescending) {
            $value *= -1;
        }

        return $value;
    }
}
