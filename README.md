<h1 name="user-content-readme-top" align="center">Wiki Plugin for Kanboard</h1>

<p align="center">
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/releases">
        <img src="https://img.shields.io/github/v/release/funktechno/kanboard-plugin-wiki?style=for-the-badge&color=brightgreen" alt="GitHub Latest Release (by date)" title="GitHub Latest Release (by date)">
    </a>
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/blob/master/LICENSE" title="Read License">
        <img src="https://img.shields.io/github/license/funktechno/kanboard-plugin-wiki?style=for-the-badge" alt="kanboard-plugin-Wysiwyg-MD-Editor">
    </a>
</p>
<p align="center">
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/releases">
        <img src="https://img.shields.io/github/downloads/funktechno/kanboard-plugin-wiki/total?style=for-the-badge&color=orange" alt="GitHub All Releases" title="GitHub All Downloads">
    </a>
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/releases">
        <img src="https://img.shields.io/github/directory-file-count/funktechno/kanboard-plugin-wiki?style=for-the-badge&color=orange" alt="GitHub Repository File Count" title="GitHub Repository File Count">
    </a>
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/releases">
        <img src="https://img.shields.io/github/repo-size/funktechno/kanboard-plugin-wiki?style=for-the-badge&color=orange" alt="GitHub Repository Size" title="GitHub Repository Size">
    </a>
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/releases">
        <img src="https://img.shields.io/github/languages/code-size/funktechno/kanboard-plugin-wiki?style=for-the-badge&color=orange" alt="GitHub Code Size" title="GitHub Code Size">
    </a>
</p>
<p align="center">
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/stargazers" title="View Stargazers">
        <img src="https://img.shields.io/github/stars/funktechno/kanboard-plugin-wiki?logo=github&style=for-the-badge" alt="kanboard-plugin-Wysiwyg-MD-Editor">
    </a>
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/discussions">
        <img src="https://img.shields.io/github/discussions/funktechno/kanboard-plugin-wiki?style=for-the-badge&color=blue" alt="GitHub Discussions" title="Read Discussions">
    </a>
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/compare">
        <img src="https://img.shields.io/github/commits-since/funktechno/kanboard-plugin-wiki/latest?include_prereleases&style=for-the-badge&color=blue" alt="GitHub Commits Since Last Release" title="GitHub Commits Since Last Release">
    </a>
    <a href="https://github.com/funktechno/kanboard-plugin-wiki/compare">
        <img src="https://img.shields.io/github/commit-activity/m/funktechno/kanboard-plugin-wiki?style=for-the-badge&color=blue" alt="GitHub Commit Monthly Activity" title="GitHub Commit Monthly Activity">
    </a>
</p>
<p align="center">
    <a href="https://github.com/kanboard/kanboard" title="Kanboard - Kanban Project Management Software">
        <img src="https://img.shields.io/badge/Plugin%20for-kanboard-D40000?style=for-the-badge&labelColor=000000" alt="Kanboard">
    </a>
</p>

---

Looking for contributors.

Wiki to document projects

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

- Simple wikipages per project
- Backup of previous versions of wikipages as editions

#### Wikilink

Find the wiki button for a project in the menu dropdown.

![Wiki link](https://github.com/funktechno/kanboard-plugin-wiki/blob/master/Asset/images/kanboard-wiki-link.png)

#### Wikipage detail

![Wikipage detail](https://github.com/funktechno/kanboard-plugin-wiki/blob/master/Asset/images/wikipage.png)

Very similar to task screen. You can edit via a modal. Copies are stored as editions. Should support uploading COMING SOON. Desired look will be to be able to reorder wikipages via dragging on left column.

### Editions Listing

![Editions Listing](https://github.com/funktechno/kanboard-plugin-wiki/blob/master/Asset/images/editionslisting.png)

Can see previous editions saved of a wikipage. Can also restore from this page.
Note that you can only restore **saved** editions. So you if you have the global setting **Saving Editions** as false then you are not currently saving editions when you make changes.

### TODO

- [x] editions listing and restore
  - Related issues: [#9](https://github.com/kanboard/kanboard/issues/9)
- [x] finish edit
- [x] ordering
  - [x] drop down to switch
  - [x] drag to move, require css magic
- [x] subpages and pagination
- [x] fix wiki sidebar
  - use html template render properly to list wiki pages
    - still having difficulty getting template helper working, manually added for each page
- [x] get rid of additional old budget plugin code
- [] kanboard rest api support - by request
- [] translations, maybe buttons, won't be translating "Wiki" for most languages
  - Related issues: [#13](https://github.com/kanboard/kanboard/issues/13), [#12](https://github.com/kanboard/kanboard/issues/12), [#10](https://github.com/kanboard/kanboard/issues/10)
- [] active, archived wikipages?
- [x] global wiki page search

### Roadmap

- style updates
- ordering
- [x] [file attachment support](https://github.com/funktechno/kanboard-plugin-wiki/issues/3)
- [x] global wiki page search [issue 5](https://github.com/funktechno/kanboard-plugin-wiki/issues/5) prob next year
- rest support - LOW PRIORITY
- [issues/bugs](https://github.com/funktechno/kanboard-plugin-wiki/issues)

### Development

After a change must update the version in Plugin.php and Makefile

Plugin.php E.g.

```php
public function getPluginVersion()
{
    return '0.2.6';
}
```

Makefile E.g.

```Makefile
version=0.2.6
```

To run a new build type `make`. NOTE: this only zips files in the last commit in the branch you are on. If you haven't commited your changes these won't be included in the zip.
  * windows install make `choco install make`

After testing create a new tag in github or via cli. `git tag -a 0.2.6 -m "Translations Updates"`. Then `git push origin --tags` Then upload the new **Wiki-version.zip** produced from `make`. Then do a pull request on https://github.com/kanboard/website for the plugins.json to update the plugin url. The `version` & `download` attributes are important to be correct.

```json
{
"wiki": {
        "title": "Wiki",
        "version": "0.2.5",
        "author": "lastlink",
        "license": "MIT",
        "description": "Wiki to document projects.",
        "homepage": "https://github.com/funktechno/kanboard-plugin-wiki",
        "readme": "https://raw.githubusercontent.com/funktechno/kanboard-plugin-wiki/master/README.md",
        "download": "https://github.com/funktechno/kanboard-plugin-wiki/releases/download/0.2.5-alpha/Wiki-0.2.5.zip",
        "remote_install": true,
        "compatible_version": ">=1.0.37"
    }
}
```
