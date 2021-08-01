<?php
/**
 * @author Leonid Eremin <leosard@yandex.ru>
 */


use baarlord\b24automationexpander\tasks\AutomationService;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\Automation\Engine\Template;
use Bitrix\Bizproc\Automation\Helper;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Bizproc\Automation\Tracker;
use Bitrix\Bizproc\Workflow\Type\GlobalConst;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;


CBitrixComponent::includeComponentClass('bitrix:bizproc.automation');

class BAEBizprocAutomationCompanent extends BizprocAutomationComponent {
    protected function __construct($component = null) {
        $parentComponent = new CBitrixComponent();
        $parentComponent->initComponent('bitrix:bizproc.automation', '.default');
        parent::__construct($parentComponent);
    }

    function executeComponent(): void {
        global $USER;
        Loader::requireModule('bizproc');
        Loader::requireModule('baarlord.b24automationexpander');
        $currentUserID = (int)$USER->GetID();
        $documentType = $this->getDocumentType();
        $documentCategoryId = $this->getDocumentCategoryId();
        $documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
        if ($this->isApiMode()) {
            $this->arResult['DOCUMENT_FIELDS'] = $this->getDocumentFields();
            $this->arResult['DOCUMENT_USER_GROUPS'] = $this->getDocumentUserGroups();
            $this->arResult['DOCUMENT_SIGNED'] = static::signDocument($documentType, $documentCategoryId, null);
            $this->arResult['DOCUMENT_NAME'] = $documentService->getEntityName($documentType[0], $documentType[1]);
            $this->includeComponentTemplate('api');
            return;
        }
        $target = null;
        if (!$this->isOneTemplateMode()) {
            /** @var BaseTarget $target */
            $target = $documentService->createAutomationTarget($documentType);
            if (!$target) {
                ShowError(Loc::getMessage('BIZPROC_AUTOMATION_NOT_SUPPORTED'));
                return;
            }
            if (!$target->isAvailable()) {
                ShowError(Loc::getMessage('BIZPROC_AUTOMATION_NOT_AVAILABLE'));
                return;
            }
        }
        $projectId = $this->getProjectId($documentType[2]);
        $automationService = new AutomationService();
        $canEdit = $automationService->isUserIsModerator($projectId, $currentUserID);
        if (!$canEdit) {
            ShowError(Loc::getMessage('BIZPROC_AUTOMATION_NO_EDIT_PERMISSIONS'));
            return;
        }
        $documentId = $this->getDocumentId();
        if ($target) {
            $target->setDocumentId($documentId);
        }
        if (isset($this->arParams['ACTION']) && $this->arParams['ACTION'] == 'ROBOT_SETTINGS') {
            $template = new Template($documentType);
            $dialog = $template->getRobotSettingsDialog($this->arParams['~ROBOT_DATA']);
            if ($dialog === '') {
                return;
            }
            if (!($dialog instanceof PropertiesDialog)) {
                ShowError('Robot dialog not supported in current context.');
                return;
            }
            if (is_array($this->arParams['~CONTEXT'])) {
                $dialog->setContext($this->arParams['~CONTEXT']);
            }
            if (mb_strpos($this->arParams['~ROBOT_DATA']['Type'], 'rest_') === 0) {
                $this->arResult = array('dialog' => $dialog);
                $this->includeComponentTemplate('rest_robot_properties_dialog');
                return;
            }
            $dialog->setDialogFileName('robot_properties_dialog');
            echo $dialog;
            return;
        }
        $statusList = $target ? $target->getDocumentStatusList($documentCategoryId) : $this->getTemplateStatusList();
        $log = array();
        if ($documentId && $target) {
            $tracker = new Tracker($target);
            $log = $tracker->getLog(array_keys($statusList));
        }
        $availableRobots = Template::getAvailableRobots($documentType);
        $triggers = array();
        if ($target) {
            $triggers = $target->getTriggers(array_keys($statusList));
            $target->prepareTriggersToShow($triggers);
        }
        $this->arResult = array(
                'CAN_EDIT' => true,
                'TITLE_VIEW' => $this->arParams['TITLE_VIEW'] ?? null,
                'TITLE_EDIT' => $this->arParams['TITLE_EDIT'] ?? null,
                'DOCUMENT_STATUS' => $target ? $target->getDocumentStatus() : null,
                'DOCUMENT_TYPE' => $documentType,
                'DOCUMENT_ID' => $documentId,
                'DOCUMENT_CATEGORY_ID' => $documentCategoryId,
                'DOCUMENT_SIGNED' => static::signDocument($documentType, $documentCategoryId, $documentId),
                'ENTITY_NAME' => $documentService->getEntityName($documentType[0], $documentType[1]),
                'STATUSES' => $statusList,
                'TEMPLATES' => $target ? $this->getTemplates(array_keys($statusList)) : array(
                        $this->prepareTemplateForView(),
                ),
                'TRIGGERS' => $triggers,
                'AVAILABLE_TRIGGERS' => $target ? $target->getAvailableTriggers() : array(),
                'AVAILABLE_ROBOTS' => array_values($availableRobots),
                'GLOBAL_CONSTANTS' => GlobalConst::getAll(),
                'DOCUMENT_FIELDS' => $this->getDocumentFields(),
                'LOG' => $log,
                'WORKFLOW_EDIT_URL' => $this->arParams['WORKFLOW_EDIT_URL'] ?? null,
                'CONSTANTS_EDIT_URL' => $this->arParams['CONSTANTS_EDIT_URL'] ?? null,
                'PARAMETERS_EDIT_URL' => $this->arParams['PARAMETERS_EDIT_URL'] ?? null,
                'STATUSES_EDIT_URL' => $this->arParams['STATUSES_EDIT_URL'] ?? null,
                'USER_OPTIONS' => array(
                        'defaults' => CUserOptions::GetOption('bizproc.automation', 'defaults', array()),
                        'save_state_checkboxes' => CUserOptions::GetOption(
                                'bizproc.automation',
                                'save_state_checkboxes',
                                array()
                        ),
                ),
                'FRAME_MODE' => $this->request->get('IFRAME') === 'Y' && $this->request->get(
                                'IFRAME_TYPE'
                        ) === 'SIDE_SLIDER',
                'USE_DISK' => Loader::includeModule('disk'),
        );
        $this->prepareDelayMinLimitResult();
        $this->includeComponentTemplate(
                'moderator_template',
                '/local/components/baarlord.b24automationexpander/bizproc.automation/templates/.default');
    }

    protected function getProjectId(string $documentType): int {
        return (int)mb_substr($documentType, mb_strlen('TASK_PROJECT_'));
    }

    protected function getDocumentFields($filter = null): array {
        return array_values(Helper::getDocumentFields($this->getDocumentType(), $filter));
    }

    protected function prepareDelayMinLimitResult(): void {
        $this->arResult['DELAY_MIN_LIMIT_M'] = 0;
        $this->arResult['DELAY_MIN_LIMIT_LABEL'] = '';
        $delayMinLimit = CBPSchedulerService::getDelayMinLimit();
        if ($delayMinLimit) {
            $this->arResult['DELAY_MIN_LIMIT_M'] = intdiv($delayMinLimit, 60);
            $this->arResult['DELAY_MIN_LIMIT_LABEL'] = Loc::getMessage(
                    'BIZPROC_AUTOMATION_DELAY_MIN_LIMIT',
                    array(
                            '#VAL#' => CBPHelper::FormatTimePeriod($delayMinLimit),
                    )
            );
        }
    }
}