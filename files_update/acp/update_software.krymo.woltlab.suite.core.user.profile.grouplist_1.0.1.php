<?php

use wcf\data\user\group\option\UserGroupOption;
use wcf\data\user\group\option\UserGroupOptionAction;
use wcf\data\user\group\option\UserGroupOptionList;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupList;
use wcf\system\cache\builder\UserGroupCacheBuilder;

/**
 * Handles the update to 1.0.1 and above from 1.0.0.
 * Migrates the old user group option values to the new user group option.
 *
 * @author      Niklas Friedrich Gerstner
 * @copyright   2020 Krymo Software
 * @license     Krymo Software - Free Products License <https://krymo.software/license-terms/#free-products>
 */

$packageID = $this->installation->getPackageID();
$oldUserGroupOption = getUserGroupOption('user.profile.canViewUserGroupList', $packageID);

if (!$oldUserGroupOption) {
    return;
}

$newUserGroupOption = getUserGroupOption('user.profile.canViewUserPageGroupList', $packageID);

if (!$newUserGroupOption) {
    return;
}

$userGroupOptionValues = [];
$userGroupList = new UserGroupList();
$userGroupList->readObjects();

foreach ($userGroupList->getObjects() as $userGroup) {
    $oldUserGroupOptionValue = $userGroup->getGroupOption($oldUserGroupOption->optionName);

    if ($oldUserGroupOptionValue) {
        $userGroupOptionValues[$userGroup->groupID] = $oldUserGroupOptionValue;
    }
}

if (!empty($userGroupOptionValues)) {
    $action = new UserGroupOptionAction([$newUserGroupOption], 'updateValues', ['values' => $userGroupOptionValues]);
    $action->executeAction();
}

/**
 * Returns a user group option based on the given option name if it exists.
 * @param $optionName   the name of the user group option to fetch from the database.
 * @param $packageID    the id of the package the user group option belongs to.
 * @return UserGroupOption|null
 */
function getUserGroupOption($optionName, $packageID) {
    $optionList = new UserGroupOptionList();
    $optionList->getConditionBuilder()->add('optionName = ?', [$optionName]);
    $optionList->getConditionBuilder()->add('packageID = ?', [$packageID]);
    $optionList->readObjects();

    if (!empty($optionList->getObjects())) {
        return $optionList->current();
    }

    return null;
}