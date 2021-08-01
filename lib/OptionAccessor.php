<?php
/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */

namespace baarlord\b24automationexpander;


use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;


class OptionAccessor {
    /**
     * @param string $moduleID
     * @param string $name
     * @param string $default
     * @param bool|string $siteId
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    function get(string $moduleID, string $name, string $default = '', $siteId = false): string {
        return Option::get($moduleID, $name, $default, $siteId);
    }
}