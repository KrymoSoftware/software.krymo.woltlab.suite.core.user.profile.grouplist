<?php

use wcf\data\language\item\LanguageItemAction;
use wcf\data\language\item\LanguageItemList;
use wcf\system\language\LanguageFactory;

/**
 * Handles the update to 1.0.3 from versions below.
 * Deletes the English language item "wcf.user.profile.groupList" (because of wrong capitalization).
 *
 * @author      Niklas Friedrich Gerstner
 * @copyright   2020 Krymo Software
 * @license     Krymo Software - Free Products License <https://krymo.software/license-terms/#free-products>
 */

$packageID = $this->installation->getPackageID();
$languageItemList = new LanguageItemList();
$languageItemList->getConditionBuilder()->add('packageID = ?', [$packageID]);
$languageItemList->getConditionBuilder()->add('languageID = ?', [LanguageFactory::getInstance()->getLanguageByCode('en')->languageID]);
$languageItemList->getConditionBuilder()->add('languageItem = ?', ['wcf.user.profile.groupList']);
$languageItemList->readObjects();

$action = new LanguageItemAction($languageItemList->getObjects(), 'delete');
$action->executeAction();