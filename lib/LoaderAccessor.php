<?php
/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */

namespace baarlord\b24automationexpander;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;


class LoaderAccessor {
    /**
     * @param string $moduleID
     * @return bool
     * @throws LoaderException
     */
    function requireModule(string $moduleID): bool {
        return Loader::requireModule($moduleID);
    }
}