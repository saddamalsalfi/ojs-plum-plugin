{**
 * plugins/generic/plumAnalytics/pageTagPlumWidget.tpl
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * Plum Analytics Widget page tag.
 *
 *}
{if $plumHtmlPrefix}{$plumHtmlPrefix}{/if}
 <!-- Plum Analytics -->
<a href="https://plu.mx/plum/a/?doi={$plumSubmissionDOI|escape}" class="{$plumWidgetType|escape}" data-hide-when-empty="{if $plumHideWhenEmpty}true{else}false{/if}" {if $plumHidePrint}data-hide-print="true" {/if}{if $plumOrientation}data-orientation="{$plumOrientation|escape}" {/if}{if $plumPopup}data-popup="{$plumPopup|escape}" {/if}{if $plumBorder}data-border="true" {/if}{if $plumWidth}data-width="{$plumWidth|escape}"{/if}></a>
<!-- /Plum Analytics -->
{if $plumHtmlSuffix}{$plumHtmlSuffix}{/if}
