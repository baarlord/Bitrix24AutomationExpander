<?php
/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */

namespace baarlord\b24automationexpander\tasks;


use baarlord\b24automationexpander\LoaderAccessor;
use baarlord\b24automationexpander\OptionAccessor;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use CSocNetUserToGroup;
use http\Exception\RuntimeException;


class AutomationService {
    protected OptionAccessor $option;

    /**
     * AutomationService constructor.
     *
     * @throws ObjectException
     */
    function __construct(LoaderAccessor $loader = null, OptionAccessor $option = null) {
        $loader = $loader ?? new LoaderAccessor();
        $this->option = $option ?? new OptionAccessor();
        try {
            $loader->requireModule('socialnetwork');
        } catch (LoaderException $e) {
            throw new ObjectException($e);
        }
    }

    function isAutomationAllowedForModerator(): bool {
        try {
            $isAllowed = $this->option->get('baarlord.b24automationexpander', 'AUTOMATION_FOR_MODERATOR');
            return $isAllowed === 'Y';
        } catch (ArgumentNullException | ArgumentOutOfRangeException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    function isUserIsModerator(int $groupId, int $userId): bool {
        $userRoles = CSocNetUserToGroup::GetList(
                array('ID' => 'DESC'),
                array('USER_ID' => $userId, 'GROUP_ID' => $groupId),
                false,
                false,
                array('ROLE')
        );
        if (!$userRole = $userRoles->Fetch()) {
            return false;
        }
        return $userRole['ROLE'] === SONET_ROLES_MODERATOR;
    }
}