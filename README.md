Wiki Plugin for Kanboard
==============================

Wiki to document projects

[![Build Status](https://travis-ci.org/kanboard/plugin-wiki.svg?branch=master)](https://travis-ci.org/kanboard/plugin-wiki)

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

Wiki is based off of taiga.io's project [wiki module](https://taiga.pm/the-wiki-module/). The budget plugin was modified to create this plugin. The main reason for this plugin is behind the need to keep project documentation together with a project and give access to the same user listing. There are many chat integrations that kanboard has and many wikis out there. However, none of the open-sourced wikis or any I'm aware of have great integrations that shared users with other solutions.

This section is available from project settings page: **Project > Wiki**. There is also a shortcut from the drop-down menu on the board.

### Supported

* Simple wikipages per project
* Backup of previous versions of wikipages as editions

#### Wikipage detail

![Wikipage detail](https://cloud.githubusercontent.com/assets/323546/20451620/965a4a2e-adc9-11e6-9131-3088ce6d8d78.png)

Very similar to task screen. You can edit via a modal. Copies are stored as editions. Should support uploading COMING SOON. Desired look will be to be able to reorder wikipages via dragging on left column.

### Editions Listing

![Editions Listing](https://cloud.githubusercontent.com/assets/323546/20451620/965a4a2e-adc9-11e6-9131-3088ce6d8d78.png)

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

### Roadmap
* style updates
* ordering
* attachment support
* rest support - LOW PRIORITY
* issues/bugs