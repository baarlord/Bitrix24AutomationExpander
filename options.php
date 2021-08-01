<?php

/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */
defined('B_PROLOG_INCLUDED') || die;


use Bitrix\Main\Localization\Loc;


/** @var string $mid */
global $USER, $APPLICATION;
if (!$USER->IsAdmin()) {
    $message = new CAdminMessage(Loc::getMessage('ACCESS_IS_DENIED'));
    echo $message->Show();
    return false;
}
$options = array(
        'groups' => array(
                array(
                        'AUTOMATION_FOR_MODERATOR',
                        Loc::getMessage('AUTOMATION_FOR_MODERATOR'),
                        false,
                        array('checkbox'),
                ),
        ),
);

$tabs = array(
        array(
                'DIV' => 'groups',
                'TAB' => Loc::getMessage('BAE_GROUPS'),
                'TITLE' => Loc::getMessage('BAE_GROUPS'),
        ),
);

if (check_bitrix_sessid()) {
    if (strlen($_POST['save']) > 0) {
        foreach ($options as $option) {
            __AdmSettingsSaveOptions($mid, $option);
        }
        LocalRedirect($APPLICATION->GetCurPageParam());
    }
}
?>
<form
        method="POST"
        action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>"
>
    <?= bitrix_sessid_post(); ?>
    <?php
    $tabControl = new CAdminTabControl('tabControl', $tabs);
    $tabControl->Begin();
    foreach ($options as $option) {
        $tabControl->BeginNextTab();
        __AdmSettingsDrawList($mid, $option);
    }
    $tabControl->Buttons(array('btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false));
    $tabControl->End(); ?>
</form>