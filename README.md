Wiki Plugin for Kanboard
==============================

Wiki to document projects

[![Build Status](https://api.travis-ci.org/funktechno/kanboard-plugin-wiki.svg?branch=master)](https://travis-ci.org/funktechno/kanboard-plugin-wiki)

- Create and manage wikipages per project
- Store different editions of wikipages

Author
------

- lastlink
- License MIT

Requirements
------------

- Kanboard >= 1.0.37

Installation
------------

You have the choice between 3 methods:

1. Install the plugin from the Kanboard plugin manager with one click
2. Download the zip file and decompress everything under the directory `plugins/Wiki`
3. Clone this repository into the folder `plugins/Wiki`

Note: Plugin folder is case-sensitive.

Documentation
-------------

### Wiki plugin

Wiki is based off of taiga.io's project [wiki module](https://taiga.pm/the-wiki-module/). The budget plugin was modified to create this plugin. The main reason for this plugin is behind the need to keep project documentation together with a project and give access to the same user listing. This is also the solution to [issue 358](https://github.com/kanboard/kanboard/issues/358). There are many chat integrations that kanboard has and many wikis out there. However, none of the open-sourced wikis or any I'm aware of have great integrations that easily share users with other solutions.

This section is available from project settings page: **Project > Wiki**. There is also a shortcut from the drop-down menu on the board.

### Supported

* Simple wikipages per project
* Backup of previous versions of wikipages as editions

#### Wikilink

Find the wiki button for a project in the menu dropdown.

![Wiki link](https://github.com/funktechno/kanboard-plugin-wiki/blob/master/Asset/images/kanboard-wiki-link.png)

#### Wikipage detail

![Wikipage detail](https://github.com/funktechno/kanboard-plugin-wiki/blob/master/Asset/images/wikipage.png)

Very similar to task screen. You can edit via a modal. Copies are stored as editions. Should support uploading COMING SOON. Desired look will be to be able to reorder wikipages via dragging on left column.

### Editions Listing

![Editions Listing](https://github.com/funktechno/kanboard-plugin-wiki/blob/master/Asset/images/editionslisting.png)

Can see previous editions saved of a wikipage. Can also restore from this page.

### TODO
* [x] editions listing and restore
* [x] finish edit
* [] ordering
    * [] drop down to switch
    * [] drag to move, require css magic
* [] fix wiki sidebar
    * use html template render properly to list wiki pages
* [x] get rid of additional old budget plugin code
* [] kanboard rest api support
* [] translations, maybe buttons, won't be translating "Wiki"
* [] active, archived wikipages?
* [] global wiki page search

### Roadmap
* style updates
* ordering
* [file attachment support](https://github.com/funktechno/kanboard-plugin-wiki/issues/3)
* global wiki page search [issue 5](https://github.com/funktechno/kanboard-plugin-wiki/issues/5) prob next year
* rest support - LOW PRIORITY
* [issues/bugs](https://github.com/funktechno/kanboard-plugin-wiki/issues)
