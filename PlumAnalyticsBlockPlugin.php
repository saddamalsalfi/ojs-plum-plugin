<?php

/**
 * @file plugins/generic/plumAnalytics/PlumAnalyticsBlockPlugin.inc.php
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 * Modified by: Saddam Al-Slfi <saddamalsalfi@qau.edu.ye>, Queen Arwa University — 2025-09-01 — OJS 3.4/3.5 upgrade
 *
 * @class PlumAnalyticsBlockPlugin
 * @ingroup plugins_generic_plumAnalytics
 *
 * @brief Class for Plum Analytics block plugin
 */
namespace APP\plugins\generic\plumAnalytics;

use PKP\plugins\BlockPlugin;
use PKP\config\Config;
use PKP\plugins\PluginRegistry;

class PlumAnalyticsBlockPlugin extends BlockPlugin {

	/** @var $parentPluginName string name of PlumAnalytics plugin */
	var $parentPluginName;

	/** @var $pluginPath string path to PlumAnalytics plugins */
	var $pluginPath;

	/**
	 * Constructor
	 * @param $parentPluginName string
	 * @param $pluginPath string
	 */
	function __construct($parentPluginName, $pluginPath) {
		parent::__construct();
		$this->parentPluginName = $parentPluginName;
		$this->pluginPath = $pluginPath;
	}

	/**
	 * Override currentVersion to prevent upgrade and delete management.
	 * @return boolean
	 */
	function getCurrentVersion() {
		return '';
	}

	/**
	 * @copydoc LazyLoadPlugin::getEnabled()
	 */
	function getEnabled($contextId = null): bool {
		if (!Config::getVar('general', 'installed')) return true;
		return parent::getEnabled($contextId);
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName(): string {
		return __('plugins.generic.plumAnalytics.block.displayName');
	}

	/**
	 * Get a description of the plugin.
	 * @return String
	 */
	function getDescription(): string {
		return __('plugins.generic.plumAnalytics.block.description');
	}

	/**
	 * Hide this plugin from the management interface (it's subsidiary)
	 * @return boolean
	 */
	function getHideManagement(): bool {
		return true;
	}

	/**
	 * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
	 * @return array
	 */
	function getSupportedContexts(): array {
		return array(BLOCK_CONTEXT_SIDEBAR);
	}

	/**
	 * Get the plum plugin
	 * @return object
	 */
	function &getPlumPlugin() {
		$plugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
		return $plugin;
	}

	/**
	 * Override the builtin to get the correct plugin path.
	 * @return string
	 */
	function getPluginPath(): string {
		return $this->pluginPath;
	}

	/**
	 * Get the name of the block template file.
	 * @return String
	 */
	function getBlockTemplateFilename(): string {
		$plugin = $this->getPlumPlugin();
		return (method_exists($plugin, 'getTemplateResource') ? '' : 'templates'.DIRECTORY_SEPARATOR) . 'blockPlumWidget.tpl';
	}

	/**
	 * @copydoc BlockPlugin::getContents()
	 */
	function getContents($templateMgr, $request = null): string {
		$plugin = $this->getPlumPlugin();
		$context = $request->getContext();
		if ($templateMgr) {
			$doi = $plugin->getSubmissionDOI($templateMgr, $context->getId(), 'block');
			if ($doi) {
				$templateMgr->assign('blockTitle', $plugin->getSetting($context->getId(), 'blockTitle'));
				$plugin->setupTemplateManager($context->getId(), $doi, $templateMgr);
				return parent::getContents($templateMgr);
			}
		}
		return '';
	}
}

?>
