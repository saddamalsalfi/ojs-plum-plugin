<?php

/**
 * @file plugins/generic/plumAnalytics/PlumAnalyticsPlugin.inc.php
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 * Modified by: Saddam Al-Slfi <saddamalsalfi@qau.edu.ye>, Queen Arwa University — 2025-09-01 — OJS 3.4/3.5 upgrade
 *
 * @class PlumAnalyticsPlugin
 * @ingroup plugins_generic_plumAnalytics
 *
 * @brief Plum Analytics plugin class
 */
namespace APP\plugins\generic\plumAnalytics;

use PKP\plugins\GenericPlugin;
use PKP\config\Config;
use PKP\plugins\PluginRegistry;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\core\JSONMessage;
use PKP\plugins\Hook;
use APP\plugins\generic\plumAnalytics\PlumAnalyticsBlockPlugin;
use APP\plugins\generic\plumAnalytics\PlumAnalyticsSettingsForm;
use APP\template\TemplateManager;


class PlumAnalyticsPlugin extends GenericPlugin {

	/**
	 * @var $settingsByWidgetType array()
	 *  This array associates widget types with the possible widget settings options
	 * _all is applied to each widget types; other array keys define widget types.
	 */
	public $settingsByWidgetType = array(
		'_all' => array('plumWidgetType', 'plumHideWhenEmpty', 'plumHook', 'plumHtmlPrefix', 'plumHtmlSuffix', 'plumBlockTitle'),
		'plumx-plum-print-popup' => array('plumPopup'),
		'plumx-summary' => array('plumOrientation', 'plumHidePrint'),
		'plumx-details' => array('plumWidth', 'plumBorder', 'plumHidePrint'),
	);

	/**
	 * @var $valuesByWidgetSetting array()
	 *  This array associates widget settings with the possible widget values options
	 */
	public $valuesByWidgetSetting = array(
		'plumPopup' => array(
			'top' => 'plugins.generic.plumAnalytics.manager.settings.popup.top',
			'bottom' => 'plugins.generic.plumAnalytics.manager.settings.popup.bottom',
			'left' => 'plugins.generic.plumAnalytics.manager.settings.popup.left',
			'right' => 'plugins.generic.plumAnalytics.manager.settings.popup.right',
			'hidden' => 'plugins.generic.plumAnalytics.manager.settings.popup.hidden',
		),
		'plumOrientation' => array(
			'vertical' => 'plugins.generic.plumAnalytics.manager.settings.orientation.vertical',
			'horizontal' => 'plugins.generic.plumAnalytics.manager.settings.orientation.horizontal',
		),
		'plumHook' => array(
			'footer' => 'plugins.generic.plumAnalytics.manager.settings.hook.footer',
			'moreInfo' => 'plugins.generic.plumAnalytics.manager.settings.hook.moreInfo',
			'details' => 'plugins.generic.plumAnalytics.manager.settings.hook.details',
			'block' => 'plugins.generic.plumAnalytics.block.displayName',
		)
	);

	/**
	 * @var $availableHooks array()
	 *  This array this the possible hooks
	 */
	public $availableHooks = array(
			'moreInfo' => 'Templates::Article::Main',
			'footer' => 'Templates::Article::Footer::PageFooter',
			'details' => 'Templates::Article::Details',
	);

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null): bool {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			// Attach to any possible hook; actual widget hook and script hook will be determined by insertWidget()
			foreach ($this->availableHooks as $k => $v) {
				Hook::add($v, array($this, 'insertWidget'));
			}

			// Load this plugin as a block plugin as well
			PluginRegistry::register(
				'blocks',
				new PlumAnalyticsBlockPlugin($this->getName(), $this->getPluginPath()),
				$this->getPluginPath()
			);
		}
		return $success;
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName(): string {
		return __('plugins.generic.plumAnalytics.displayName');
	}

	/**
	 * Get a description of the plugin.
	 * @return String
	 */
	function getDescription(): string {
		return __('plugins.generic.plumAnalytics.description');
	}

	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $verb): array {
		$router = $request->getRouter();
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $verb)
		);
	}

	/**
	 * Insert Plum Analytics page tag to footer, if page is an Article
	 * @param $hookName string Name of hook calling function
	 * @param $params array of smarty and output objects
	 * @return boolean
	 */
	function insertWidget($hookName, $params) {
		$output =& $params[2];
		$request = $this->getRequest();
		$templateMgr = TemplateManager::getManager($request);
		$context = $request->getContext();
		$doi = $this->getSubmissionDOI($templateMgr, $context->getId(), $hookName);
		if ($doi) {
			if ($hookName == 'Templates::Article::Footer::PageFooter') {
				$target = $this->getTemplateResource('pageTagPlumScript.tpl');
				$output .= $templateMgr->fetch($target);
			}
			if ($this->availableHooks[$this->getSetting($context->getId(), 'hook')] == $hookName) {
				$this->setupTemplateManager($context->getId(), $doi, $templateMgr);
				$target = $this->getTemplateResource('pageTagPlumWidget.tpl');
				$output .= $templateMgr->fetch($target);
			}
		}
		return false;
	}

	/**
	 * Set required variables in the Template Manager
	 * @param $contextId integer Context Id
	 * @param $doi string DOI
	 * @param $templateMgr object Template Manager
	 */
	function setupTemplateManager($contextId, $doi, $templateMgr) {
		// Assign the article identifier
		$templateMgr->assign('plumSubmissionDOI', $doi);
		// Assign variables required by all widgetTypes
		foreach ($this->settingsByWidgetType['_all'] as $k) {
			// database setting is stored without "plum" prefix, e.g. plumSettingName is settingName
			$v = $this->getSetting($contextId, lcfirst(substr($k, 4)));
			$templateMgr->assign($k, $v);
		}
		// Assign variables as dictated by the settingsByWidgetType association
		foreach ($this->settingsByWidgetType[$this->getSetting($contextId, 'widgetType')] as $k) {
			// database setting is stored without "plum" prefix, e.g. plumSettingName is settingName
			$templateMgr->assign($k, $this->getSetting($contextId, lcfirst(substr($k, 4))));
		}
		$templateMgr->assign('plumWidgetTemplatePath', $this->getTemplateResource('pageTagPlumWidget.tpl'));
	}

	/**
	 * Check to see if the context of the Template Manger will support the widget
	 * @param $templateMgr object Template Manager
	 * @param $context int Context ID
	 * @param $hookName string the name of the hook calling this function
	 * @return string DOI, if present and plugin settings are configured appropriately
	 */
	function getSubmissionDOI($templateMgr, $context, $hookName) {
		// Use the request and page router to get the requested page
		$request = $this->getRequest();
		$router = $request->getRouter();
		// Shortcut this function if we are not in an article, or not in the selected hook, or not in the PageFooter
		if ($router->getRequestedPage($request) != 'article' || ($hookName != $this->availableHooks[$this->getSetting($context, 'hook')] && !($hookName == 'block' && $this->getSetting($context, 'hook') == 'block') && $hookName != 'Templates::Article::Footer::PageFooter')) {
			return false;
		}

		// submission is required to retreive DOI
		$submission = null;
		if (method_exists($templateMgr, 'get_template_vars')) {
			// Smarty 2
			$submission = $templateMgr->get_template_vars('article');
		} else if (method_exists($templateMgr, 'getTemplateVars')) {
			// Smarty 3
			$submission = $templateMgr->getTemplateVars('article');
		}
		if (!$submission) {
			return false;
		}

		// requested page must be a submission with a DOI for widget display
		$doi = $submission->getStoredPubId('doi');
		if (!$doi) {
			return false;
		}

		// sanity check to ensure values required by _all widgets are included
		$requiredValues = true;
		foreach (array('widgetType', 'hook') as $k) {
			$v = $this->getSetting($context, $k);
			if (!$v) {
				$requiredValues = false;
			}
		}
		return ($requiredValues ? $doi : '');
	}

	/**
	 * @copydoc Plugin::manage()
	 */
	function manage($args, $request) {
		$verb = $request->getUserVar('verb');
		switch ($verb) {
			case 'settings':
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->registerPlugin('function', 'plugin_url', array($this, 'smartyPluginUrl'));
				$context = $request->getContext();
				$form = new PlumAnalyticsSettingsForm($this, $context->getId());
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}

	/**
	 * @copydoc Plugin::getTemplatePath()
	 */
	function getTemplatePath($inCore = false): string {
		return parent::getTemplatePath($inCore) . 'templates' . DIRECTORY_SEPARATOR;
	}

}
?>
