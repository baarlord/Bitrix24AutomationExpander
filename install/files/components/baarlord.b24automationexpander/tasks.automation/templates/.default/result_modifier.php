<?php
/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */
defined('B_PROLOG_INCLUDED') || die;


use Bitrix\Main\Localization\Loc;
use Bitrix\Tasks\Integration\Bizproc\Document\Task;


/**
 * @var CMain $APPLICATION
 * @var array $arResult
 */
if (Task::isProjectTask($arResult['DOCUMENT_TYPE'])) {
    $arResult['TITLE_VIEW'] = Loc::getMessage('TASKS_AUTOMATION_CMP_TITLE_VIEW');
    $arResult['TITLE_EDIT'] = Loc::getMessage('TASKS_AUTOMATION_CMP_TITLE_TASK_EDIT');
} elseif (Task::isPlanTask($arResult['DOCUMENT_TYPE'])) {
    $arResult['TITLE_VIEW'] = Loc::getMessage('TASKS_AUTOMATION_CMP_TITLE_VIEW_PLAN_1');
    $arResult['TITLE_EDIT'] = Loc::getMessage('TASKS_AUTOMATION_CMP_TITLE_TASK_EDIT_PLAN_1');
} else {
    $arResult['TITLE_VIEW'] = Loc::getMessage('TASKS_AUTOMATION_CMP_TITLE_VIEW_STATUSES');
    $arResult['TITLE_EDIT'] = Loc::getMessage('TASKS_AUTOMATION_CMP_TITLE_TASK_EDIT_STATUSES');
}
if ($arResult['TASK_CAPTION']) {
    $arResult['TITLE_VIEW'] = Loc::getMessage('TASKS_AUTOMATION_CMP_TITLE_TASK_VIEW', array('#TITLE#' => $arResult['TASK_CAPTION']));
}
$arResult['DOCUMENT_TYPE'] = array('tasks', Task::class, $arResult['DOCUMENT_TYPE']);
$arResult['DOCUMENT_ID'] = $arResult['TASK_ID'] ?: null;
