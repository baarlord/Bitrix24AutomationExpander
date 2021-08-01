<?php
/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */

namespace baarlord\b24automationexpander\tasks;


use baarlord\b24automationexpander\LocAccessor;
use Bitrix\Main\ObjectException;
use CAllMain;
use CComponentEngine;
use CSite;
use Exception;


class InterfaceMutator {
    protected AutomationService $as;
    protected int $userId;
    protected CAllMain $app;
    protected LocAccessor $loc;

    /**
     * InterfaceMutator constructor.
     *
     * @param int $userId
     * @param AutomationService|null $as
     * @param CAllMain|null $app
     * @param LocAccessor|null $loc
     * @throws ObjectException
     */
    function __construct(
            int $userId,
            AutomationService $as = null,
            CAllMain $app = null,
            LocAccessor $loc = null
    ) {
        global $APPLICATION, $USER;
        try {
            $this->userId = $userId;
            $this->as = $as ?? new AutomationService();
            $this->loc = $loc ?? new LocAccessor();
            $this->app = $app ?? $APPLICATION;
        } catch (Exception $e) {
            throw new ObjectException($e);
        }
    }

    /**
     * @throws ObjectException
     */
    static function showAutomationButtonForModerator(): void {
        global $USER;
        if (CSite::InDir('/bitrix/') || (int)$USER->GetID() <= 0) {
            return;
        }
        $mutator = new InterfaceMutator($USER->GetID());
        if (!$mutator->as->isAutomationAllowedForModerator()) {
            return;
        }
        $urlTemplates = array('tasks' => 'group/#GROUP_ID#/tasks/');
        $sefFolder = '/workgroups/';
        $variables = array();
        $page = CComponentEngine::ParseComponentPath($sefFolder, $urlTemplates, $variables);
        if (($page !== 'tasks') || empty($variables['GROUP_ID'])) {
            return;
        }
        if (!$mutator->as->isUserIsModerator($variables['GROUP_ID'], $mutator->userId)) {
            return;
        }
        $mutator->drawButtonForModerators($variables['GROUP_ID']);
    }

    function drawButtonForModerators(int $groupID): void {
        $sliderPath = '/local/components/baarlord.b24automationexpander/tasks.automation/slider.php?site_id=' .
                SITE_ID . '&' . bitrix_sessid_get() . '&project_id=' . $groupID;
        $sliderParams = '{allowChangeHistory: false}';
        $html = '<div class="tasks-counter-btn-container">' .
                '<button ' .
                'class="ui-btn ui-btn-light-border ui-btn-no-caps ui-btn-themes ui-btn-round tasks-counter-btn "' .
                ' onclick="BX.SidePanel.Instance.open(\'' . $sliderPath . '\', ' . $sliderParams . ')"' .
                '>' . $this->loc->getMessage('AUTOMATION_FOR_MODERATORS') . '</button>' .
                '</div>';
        $this->app->AddViewContent('below_pagetitle', $html);
    }
}