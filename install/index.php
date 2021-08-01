<?php
/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */

defined('B_PROLOG_INCLUDED') || die;


use Bitrix\Main\Context;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;


class baarlord_b24automationexpander extends CModule {
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_ID = 'baarlord.b24automationexpander';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function __construct() {
        Loc::loadLanguageFile(__FILE__);
        $moduleVersion = array();
        include(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $moduleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $moduleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('BAE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('BAE_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('BAE_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('BAE_PARTNER_URI');
    }

    function DoInstall() {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallEvents();
        $this->InstallFiles();;
    }

    function DoUninstall() {
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * @throws LoaderException
     */
    function InstallEvents() {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
                'main',
                'onEpilog',
                $this->MODULE_ID,
                '\baarlord\b24automationexpander\tasks\InterfaceMutator',
                'showAutomationButtonForModerator',
        );
    }

    /**
     * @throws LoaderException
     */
    function UnInstallEvents() {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
                'main',
                'onEpilog',
                $this->MODULE_ID,
                '\baarlord\b24automationexpander\tasks\InterfaceMutator',
                'showAutomationButtonForModerator',
        );
    }

    function InstallFiles() {
        $context = Context::getCurrent();
        $server = $context->getServer();
        CopyDirFiles(
                __DIR__ . '/files/components/baarlord.b24automationexpander',
                $server->getDocumentRoot() . '/local/components/baarlord.b24automationexpander',
                true,
                true
        );
    }

    function UnInstallFiles() {
        DeleteDirFilesEx('/local/components/baarlord.b24automationexpander');
    }
}