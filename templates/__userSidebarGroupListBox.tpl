{if PROFILE_ENABLE_GROUPLIST && $userGroups|count && $__wcf->getSession()->getPermission('user.profile.canViewUserGroupList')}
    <section class="box" data-static-box-identifier="software.krymo.woltlab.suite.core.user.profile.grouplist.UserGroupList">
        <h2 class="boxTitle">{lang}wcf.user.profile.grouplist{/lang} <span class="badge">{#$userGroups|count}</span></h2>

        <div class="boxContent">
            <ul class="userGroupList">
                {foreach from=$userGroups item=userGroup}
                    <li>{$userGroup->getName()}</li>
                {/foreach}
            </ul>
        </div>
    </section>
{/if}