<?php

/**
 * @file plugins/generic/plumAnalytics/PlumAnalyticsSettingsForm.inc.php
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 * Modified by: Saddam Al-Slfi <saddamalsalfi@qau.edu.ye>, Queen Arwa University — 2025-09-01 — OJS 3.4/3.5 upgrade
 *
 * @class PlumAnalyticsSettingsForm
 * @ingroup plugins_generic_plumAnalytics
 *
 * @brief Form for journal managers to modify Plum Analytics plugin settings
 */
namespace APP\plugins\generic\plumAnalytics;

use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorPost;
use APP\template\TemplateManager;

class PlumAnalyticsSettingsForm extends Form {

	/** @var $contextId int */
	var $_contextId;

	/** @var $plugin PlumAnalyticsPlugin */
	var $_plugin;

	/** @var $widgetTypes array() hash of valid widget type options, for use in the form select element */
	var $widgetTypes;
	
	/** @var $options array() hash of valid widget settings options */
	var $options;
	
	/** @var $options array() convenience variable for each keyname for retrieving settings */
	var $settingsKeys;
	
	/**
	 * Constructor
	 * @param $plugin PlumAnalyticsPlugin
	 * @param $contextId int
	 */
	function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;
		
		// Set options for widgetTypes, and setup convenience variable for settings iterators
		$this->widgetTypes = array();
		$this->settingsKeys = array();
		$this->widgetTypes[''] = '';
		foreach ($plugin->settingsByWidgetType as $k => $v) {
			$this->widgetTypes[$k] = 'plugins.generic.plumAnalytics.manager.settings.widgetType.'.$k;
			$this->settingsKeys = array_merge($this->settingsKeys, $v);
		}
		unset($this->widgetTypes['_all']);
		$this->settingsKeys = array_unique($this->settingsKeys);
		// Set options for popup alignment
		$this->options = array();
		foreach ($plugin->valuesByWidgetSetting as $k => $v) {
			$this->options[$k] = array_merge(array('' => ''), $v);
		}
		
		parent::__construct(method_exists($plugin, 'getTemplateResource') ? $plugin->getTemplateResource('settingsForm.tpl') : $plugin->getTemplatePath() . 'settingsForm.tpl');

		$this->addCheck(new FormValidator($this, 'plumWidgetType', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.plumAnalytics.manager.settings.widgetTypeRequired'));
		$this->addCheck(new FormValidator($this, 'plumHook', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.plumAnalytics.manager.settings.hookRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$contextId = $this->_contextId;
		$plugin =& $this->_plugin;

		parent::initData();
		foreach ($this->settingsKeys as $k) {
			// database setting is stored without "plum" prefix, e.g. plumSettingName is settingName
			$this->setData($k, $plugin->getSetting($contextId, lcfirst(substr($k, 4))));
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars($this->settingsKeys);
	}

	/**
	 * Fetch the form.
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = NULL, $display = false): string {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		// This assigns select options
		foreach ($this->options as $k => $v) {
			$templateMgr->assign($k.'Types', $v);
		}
		$templateMgr->assign('plumWidgetTypes', $this->widgetTypes);
		$templateMgr->assign('plumAllWidgetSettings', $this->settingsKeys);
		$hideSettings = array('' => array());
		foreach ($this->_plugin->settingsByWidgetType as $k => $v) {
			$hideSettings[$k] = array_diff($this->settingsKeys, array_merge($this->_plugin->settingsByWidgetType[$k], $this->_plugin->settingsByWidgetType['_all']));
		}
		$templateMgr->assign('plumWidgetHideSettings', $hideSettings);
		return parent::fetch($request, $template = NULL, $display = false);
	}

	/**
	 * Save settings.
	 */
	function execute(...$functionArgs) {
		$plugin =& $this->_plugin;
		$contextId = $this->_contextId;

		foreach ($this->settingsKeys as $k) {
			$saveData = $this->getData($k);
			$saveType = 'string';
			// special handling of checkboxes
			if (in_array($k, array('plumHideWhenEmpty', 'plumHidePrint', 'plumBorder'))) {
				$saveType = 'bool';
				$saveData = boolval($saveData);
			}
			// database setting is stored without "plum" prefix, e.g. plumSettingName is settingName
			$plugin->updateSetting($contextId, lcfirst(substr($k, 4)), $saveData, $saveType);
		}
	}
	
}

?>
