<?php
/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */
defined('B_PROLOG_INCLUDED') || die;


use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;


/**
 * @var CMain $APPLICATION
 * @var array $arResult
 * @var BAETasksAutomationComponent $component
 */
CUtil::initJSCore('tasks_integration_socialnetwork');
?>
<div class="tasks-automation">
    <?php
    $APPLICATION->IncludeComponent(
            'baarlord.b24automationexpander:bizproc.automation',
            '',
            array(
                    'DOCUMENT_TYPE' => $arResult['DOCUMENT_TYPE'],
                    'DOCUMENT_ID' => $arResult['DOCUMENT_ID'],
                    'TITLE_VIEW' => $arResult['TITLE_VIEW'],
                    'TITLE_EDIT' => $arResult['TITLE_EDIT'],
                    'MARKETPLACE_ROBOT_CATEGORY' => 'tasks_bots',
                    'MARKETPLACE_TRIGGER_PLACEMENT' => 'TASKS_ROBOT_TRIGGERS',
                    'MESSAGES' => array(
                            'BIZPROC_AUTOMATION_CMP_TRIGGER_HELP_2' => Loc::getMessage(
                                    'TASKS_AUTOMATION_CMP_TRIGGER_HELP_TIP_2'
                            ),
                            'BIZPROC_AUTOMATION_CMP_ROBOT_HELP' => Loc::getMessage(
                                    'TASKS_AUTOMATION_CMP_ROBOT_HELP_TIP'
                            ),
                            'BIZPROC_AUTOMATION_CMP_ROBOT_HELP_ARTICLE_ID' => '8233939',
                    ),
            ),
            $component
    ); ?>
</div>
<script>
    BX.ready(function () {
        let viewType = '<?=CUtil::JSEscape($arResult['VIEW_TYPE'])?>';
        let toolbarNode = document.querySelector('[data-role="automation-base-toolbar"]');
        if (!toolbarNode) {
            return;
        }

        let selectorNode = BX.create('button', {
            attrs: {className: 'ui-btn ui-btn-light-border ui-btn-dropdown tasks-automation-group-selector'},
            text: '<?=CUtil::JSEscape($arResult['GROUPS_SELECTOR']['CAPTION'])?>'
        });

        if (viewType === 'plan') {
            selectorNode.textContent = '<?=GetMessageJS('TASKS_AUTOMATION_CMP_SELECTOR_ITEM_PLAN_1')?>';
        } else if (viewType === 'personal') {
            selectorNode.textContent = '<?=GetMessageJS('TASKS_AUTOMATION_CMP_SELECTOR_ITEM_PERSONAL')?>';
        }

        toolbarNode.insertBefore(selectorNode, toolbarNode.lastElementChild);

        let menu = null;
        let groups = <?=Json::encode($arResult['GROUPS_SELECTOR']['GROUPS'])?>;
        let currentGroupId = <?= (int)$arResult['PROJECT_ID']?>;

        BX.bind(selectorNode, 'click', function (event) {
            if (menu === null) {
                let projectMenuItems = [];

                let clickHandler = function (e, item) {
                    menu.close();
                    if (item.id === currentGroupId && viewType === 'project') {
                        return;
                    }
                    selectorNode.innerHTML = item.text;
                    window.location.href = BX.util.add_url_param(window.location.href, {
                        project_id: item.id,
                        view: 'project'
                    });
                };

                for (var i = 0, c = groups.length; i < c; i++) {
                    projectMenuItems.push({
                        id: parseInt(groups[i]['id']),
                        text: BX.util.htmlspecialchars(groups[i]['text']),
                        class: 'menu-popup-item-none',
                        onclick: BX.delegate(clickHandler, this)
                    });

                }
                if (groups.length > 0) {
                    projectMenuItems.push({delimiter: true});
                    projectMenuItems.push({
                        id: 'new',
                        text: '<?=GetMessageJS('TASKS_AUTOMATION_CMP_CHOOSE_GROUP')?>',
                        onclick: function (event, item) {
                            menu.getPopupWindow().setAutoHide(false);
                            var selector = new BX.Tasks.Integration.Socialnetwork.NetworkSelector({
                                scope: item.getContainer(),
                                id: 'group-selector',
                                mode: 'group',
                                query: false,
                                useSearch: true,
                                useAdd: false,
                                parent: this,
                                popupOffsetTop: 5,
                                popupOffsetLeft: 40
                            });

                            selector.bindEvent('item-selected', function (data) {
                                clickHandler(null, {
                                    id: data.id,
                                    text: data.nameFormatted.length > 50
                                        ? data.nameFormatted.substring(0, 50) + '...'
                                        : data.nameFormatted
                                });
                                selector.close();
                            });

                            selector.bindEvent('close', function (data) {
                                menu.getPopupWindow().setAutoHide(true);
                            });

                            selector.open();
                        }
                    });
                }
                menu = BX.PopupMenu.create(
                    'tasks-automation-view-selector-' + BX.util.getRandomString(),
                    selectorNode,
                    [
                        {
                            text: '<?=GetMessageJS('TASKS_AUTOMATION_CMP_SELECTOR_ITEM_PROJECTS')?>',
                            items: projectMenuItems
                        },
                        {
                            text: '<?=GetMessageJS('TASKS_AUTOMATION_CMP_SELECTOR_ITEM_PLAN_1')?>',
                            onclick: function (e, item) {
                                menu.close();
                                selectorNode.textContent = item.text;
                                window.location.href = BX.util.add_url_param(window.location.href, {view: 'plan'});
                            }
                        },
                        {
                            text: '<?=GetMessageJS('TASKS_AUTOMATION_CMP_SELECTOR_ITEM_PERSONAL')?>',
                            onclick: function (e, item) {
                                menu.close();
                                selectorNode.textContent = item.text;
                                window.location.href = BX.util.add_url_param(window.location.href, {view: 'personal'});
                            }
                        }
                    ],
                    {
                        autoHide: true,
                        closeByEsc: true
                    }
                );
            }
            menu.popupWindow.show();
        });
    });
</script>
