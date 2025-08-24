{**
 * plugins/generic/plumAnalytics/settingsForm.tpl
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * Plum Analytics plugin settings
 *
 *}
<script>
	{literal}
	/*
	 *  Set visiblity of options based on Widget Type selection
	 */
	function plumSetOptionVisibility() {
		{/literal}
			{foreach from=$plumAllWidgetSettings item=plumxWidgetSetting}
				{if $plumxWidgetSetting != "plumBlockTitle"}
				$('#{$plumxWidgetSetting|escape}Container').show();
				{/if}
			{/foreach}
		{literal}
		switch ($('#plumWidgetType').val()) {
			{/literal}
			{foreach from=$plumWidgetTypes item=plumxWidgetText key=plumxWidgetType}
				case "{$plumxWidgetType|escape}":
					{foreach from=$plumWidgetHideSettings[$plumxWidgetType] item=plumxWidgetSetting}
						$('#{$plumxWidgetSetting|escape}Container').hide();
					{/foreach}
					break;
			{/foreach}
			{literal}
		}
	}
	/*
	 *  Set visiblity of block title based on Widget Display selection
	 */
	function plumSetBlockTitleVisibility() {
		if ($('#plumHook').val() !== 'block') {
			$('#plumBlockTitleContainer').hide();
		} else {
			$('#plumBlockTitleContainer').show();
		}
	}
	$(function() {
		// Attach the form handler.
		$('#plumSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
		// Attach onchange handlers
		$('#plumHook').change(
			function () {
				plumSetBlockTitleVisibility();
			}
		);
		$('#plumWidgetType').change(
			function () {
				plumSetOptionVisibility();
			}
		);
	});
	// set initial form state
	plumSetOptionVisibility();
	plumSetBlockTitleVisibility();
	// apply containers not able to be set by fbv
	$('#plumCheckboxList li input').each(
		function () {
			$(this).closest('li').attr('id', $(this).attr('id')+'Container');
		}
	);
	{/literal}
</script>

<form class="pkp_form" id="plumSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="plumSettingsFormNotification"}

	<div id="description">{translate key="plugins.generic.plumAnalytics.manager.settings.description"}</div>

	{fbvFormArea id="plumSettingsFormArea"}
		{fbvFormSection for="plumWidgetType" description="plugins.generic.plumAnalytics.manager.settings.widgetType"}
			{fbvElement type="select" id="plumWidgetType" from=$plumWidgetTypes selected=$plumWidgetType translate=true size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection list="true" id="plumCheckboxList"}
			{fbvElement type="checkbox" id="plumHideWhenEmpty" label="plugins.generic.plumAnalytics.manager.settings.hideWhenEmpty" checked=$plumHideWhenEmpty|compare:true}
			{fbvElement type="checkbox" id="plumBorder" label="plugins.generic.plumAnalytics.manager.settings.border" checked=$plumBorder|compare:true}
			{fbvElement type="checkbox" id="plumHidePrint" label="plugins.generic.plumAnalytics.manager.settings.hidePrint" checked=$plumHidePrint|compare:true}
		{/fbvFormSection}
		{fbvFormSection for="plumPopup" description="plugins.generic.plumAnalytics.manager.settings.popup" id="plumPopupContainer"}
			{fbvElement type="select" id="plumPopup" from=$plumPopupTypes selected=$plumPopup translate=true size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection for="plumOrientation" description="plugins.generic.plumAnalytics.manager.settings.orientation" id="plumOrientationContainer"}
			{fbvElement type="select" id="plumOrientation" from=$plumOrientationTypes selected=$plumOrientation translate=true size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection for="plumWidth" description="plugins.generic.plumAnalytics.manager.settings.width" id="plumWidthContainer"}
			{fbvElement type="text" id="plumWidth" value=$plumWidth inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection description="plugins.generic.plumAnalytics.manager.settings.hook"}
			<div class="instructions">{translate key="plugins.generic.plumAnalytics.manager.settings.hookInstructions"}</div>
			{fbvElement type="select" id="plumHook" from=$plumHookTypes selected=$plumHook translate=true size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection description="plugins.generic.plumAnalytics.manager.settings.blockTitle" id="plumBlockTitleContainer"}
			{fbvElement type="text" id="plumBlockTitle" value=$plumBlockTitle inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection label="plugins.generic.plumAnalytics.manager.settings.htmlPrefix" for="plumHtmlPrefix"}
			{fbvElement type="textarea" multilingual=false name="plumHtmlPrefix" id="plumHtmlPrefix" value=$plumHtmlPrefix rich=true height=$fbvStyles.height.TALL}
		{/fbvFormSection}
		{fbvFormSection label="plugins.generic.plumAnalytics.manager.settings.htmlSuffix" for="plumHtmlSuffix"}
			{fbvElement type="textarea" multilingual=false name="plumHtmlSuffix" id="plumHtmlSuffix" value=$plumHtmlSuffix rich=true height=$fbvStyles.height.TALL}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
