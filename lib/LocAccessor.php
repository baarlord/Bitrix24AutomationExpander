<?php
/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */

namespace baarlord\b24automationexpander;


use Bitrix\Main\Localization\Loc;


class LocAccessor {
    /**
     * @param $code
     * @param array|null $replace
     * @param string|null $language
     * @return string
     */
    function getMessage($code, array $replace = null, string $language = null): string {
        return Loc::getMessage($code, $replace, $language);
    }
}