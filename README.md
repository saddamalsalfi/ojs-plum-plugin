Unofficial fork of ulsdevteam/ojs-plum-plugin. Upgraded/maintained for OJS 3.4/3.5 by Saddam Al-Slfi.
saddamalsalfi@qau.edu.ye, Queen Arwa University

# Plum Analytics Artifact Widget plugin for OJS

This plugin provides display of PlumX Metrics from [Plum Analytics](http://plumanalytics.com/) on the article level for PKP Open Journal Systems.

## Requirements

* OJS 3.5
* Article level DOIs
  * see: Login -> Settings -> Website -> Plugins -> DOI
* PlumX tracking of the artifacts with those DOIs
  * this coverage occurs by one or more of:
    * Representation in a source like CrossRef, PubMed, Scopus, Science Direct, SSRN, EBSCOhost, or others
    * a request for harvesting via OAI-PMH
    * a customer relationship for custom harvesting
  * see: [Plum Analytics OJS Integration](https://plumanalytics.com/integrate/load-your-data/ojs_integration/)

## Configuration

### Plugin Gallery installation

Install this as a "generic" plugin in OJS.  The preferred installation method is through the Plugin Gallery.

### Manual installation

To install manually via the filesystem, extract the contents of this archive to a "plumAnalytics" directory under "plugins/generic" in your OJS root.

To install via Git submodule, target that same directory path: 
```
git submodule add https://github.com/ulsdevteam/ojs-plum-plugin plugins/generic/plumAnalytics
```

Installation via the Plugin Gallery will automatically register the plugin.  If not installed via the Plugin Gallery, run the install or upgrade script to register the plugin, e.g.: 
```
php lib/pkp/tools/installPluginVersion.php plugins/generic/plumAnalytics/version.xml
```

### Enable and Setup

Login as a Journal Manager and navigate to the Journal for which you wish to use the Widget.  Enable the plugin via Login -> Settings -> Website -> Plugins -> Plum Analytics Artifact Widget -> Enable.

To configure the plugin, you will need to select what type of widget you want, and where it will display.  Additional options may apply to specific widget types.  See the [PlumX Widgets page](https://plu.mx/developers/widgets) for an overview of the different widget types and options.

## Author / License

Written by Clinton Graham and Tazio Polanco for the [University of Pittsburgh](http://www.pitt.edu).  Copyright (c) University of Pittsburgh.

Released under a license of GPL v2 or later.
