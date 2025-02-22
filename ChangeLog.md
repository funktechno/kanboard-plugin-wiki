# Changelog

## Version 0.4.0
* contributors: @imfx77

---

### New Features:
* Fully reworked the `Wiki Content` hierarchy, it is contained in a standalone resizable sidebar of the Wiki **Details** and **Editions** pages.
It supports unlimited nesting of elements and **drag & drop** to reorganize them in arbitrary fashion.
It has convenient visual aids now and and all the bugs or limitations regarding items rearrangement have been resolved.
The project header and dropdown Wiki links now redirect by default to the **(root)** of the `Wiki Content`.
The Wiki tree now also works on mobile, yet it uses explicit handles for reordering because of the touch and slide mechanics.
Anyway, better try it than explain it further 😎
* Wiki **Overview** now shows an indented list to denote hierarchy.
And also does the parent dropdown in the Wiki page **Edit** modal.
* Implemented purging of Wiki **Editions** (alongside the Restore functionality).
Updating an **Edition** now checks for actual changes
and if nothing meaningful has changed (title and content) skips creating the edition. 
Actions like changing the parent shouldn't trigger new editions to avoid unnecessary multiplication of the data.

### Improvements:
* Massive improvements to UI/UX.
* Fixed titles of pages to match projects.
* Enhanced details and search on Wiki **Index**.
* Wiki pages content is now expandable on demand (instead of preloaded) for the Wiki **Index** and **Editions**. 
* Multiple adjustments for styles, colors, positions and layouts of global containers and page elements.
* Adjusted layouts of elements in create/edit wiki modals, unified the modals themselves.
* All buttons now have tooltips (yet not translations).
* Massive cleanup of redundant and bloated code, misleading comment leftovers, unused method parameters, etc.
* Quite some refactoring on demand and on the go.
* Naming unification for styles, elements, methods, parameters.
* Reducing copy pasted and coupled code.
* Code syntax formatting and beatification for better readability.
* Improved this very `ChangeLog`, transforming it into MD. 

### Bug fixes:
* Fixed file viewer
* Fixed routes for all controller actions in use.
They used to be copy pasted and were useless as they were all the same.
Minimalistic possible and sensible notation is proposed.
* Fixed the bug that a wiki page could assign a parent from its own subtree,
thus effectively and irreversibly breaking the wiki page hierarchy.
Now the parent dropdown in the **Edit** modal shows only the possible parent nodes.
* Fixed the `jquery-sortable.js` library (which is not supported and maintained for quite a while),
to work on mobile and to allow correct touch and **drag & drop** elements without sliding the whole page along.
* Obviously, all the functionality about managing wikipages' `ordercolumn` has been totally revamped to guarantee correct sequential numbers for subpages.
* Various small bugfixes.

## Version 0.3.9

---

### Bug fixes:

* Fix https://github.com/funktechno/kanboard-plugin-wiki/issues/99

## Version 0.3.8

---

### Improvements:

* Fixed file viewer for attachments.
* Added screenshot support.
* fixes for composer install support

## Version 0.3.7

---

### Improvements:

* unit test updates
* code cleanup
* Add Wiki entry to views switcher
* only load nested vendor on detail page

### Bug fixes:

* Fix https://github.com/funktechno/kanboard-plugin-wiki/issues/61
* Fix https://github.com/funktechno/kanboard-plugin-wiki/issues/11

## Version 0.3.6

---

### Improvements:

* fix missing changes for sub pages

### Bug fixes:

* Fix https://github.com/funktechno/kanboard-plugin-wiki/issues/11

## Version 0.3.5

---

### Improvements:

* ability to organize pages into sub pages

### Bug fixes:

* Fix https://github.com/funktechno/kanboard-plugin-wiki/issues/11

## Version 0.3.4

---

### Improvements:

* ability to reorder wiki pages

### Bug fixes:

* Fix https://github.com/funktechno/kanboard-plugin-wiki/issues/28

## Version 0.3.3

---

### Improvements:

* mysql character set update for emojis
* synfony deprecation fix
* public wiki link to board
* added Ukrainian translation

### Bug fixes:

* Fix https://github.com/funktechno/kanboard-plugin-wiki/issues/39
* Fix https://github.com/funktechno/kanboard-plugin-wiki/issues/60
* Fix https://github.com/funktechno/kanboard-plugin-wiki/issues/56
* Fix `'max_size' => get_upload_max_size(),`

## Version 0.3.2

---

### Improvements:

* width updates to wiki table list
* public mode
* initialize notifications(?) maybe works
* some syntax error fixes

## Version 0.3.1-alpha

---

### Bug fixes:

* On remove Wiki page, also remove all associated files. Fixes #34.
* Setting 'Save editions' made changeable, stored permanently. Fixes #43.

### Improvements:

* make config settings variable so that they can be translated
* french translation improvements

## Version 0.3.0-alpha

---

### Improvements:

* left align menu sidebar

## Version 0.2.9-alpha

---

### Improvements:

* File and Image Attachments Feature
  * Added Upload Support
  * Added Thumbnail Support
  * Added Download Support
  * Added Removal Support
  * Added Slideshow Support for images
* Updated French translation

## Version 0.2.8-alpha

---

### Improvements:

* Global Wiki search feature
  * added ability to list all wikipages (paginated) as a high level overview
  * Can order them
  * Also can search by content.
* Some Translation/grammar cleanups
  * removed ': ' from translations
  * Cleaned up some gramar
  * Renamed wikipage to Wiki page
* Updated sidebar
  * removed excess space from left side

## Version 0.2.7-alpha

---

### Bug fixes:

* Update mysql wiki table encoding to utf8 to support foreign characters

## Version 0.2.6-alpha

---

### Improvements:

* updated some translations
  * Russian updated #10
  * Updated translation for New wikipage (incorrectly was New wiki line)
  * Missing translation empty attributes added
  * Portuguese & Spanish updated
  * 'Wiki' Translation commented out in most language. Was defaulting to budget.
* Some linting fixes on readme files

## Version 0.2.5-alpha

---

### Bug fixes:

* updated getPluginHomepage to proper link.

## Version 0.2.4-alpha

---

### Bug fixes:

* extra unused template action removed

## Version 0.2.3-alpha

---

### Bug fixes:

* fixed order column bug for sqlite

## Version 0.2.2-alpha

---

### New features:

* dragable wikipages ui for reordering, does not persist changes reordering

### Bug fixes:

* tested and fixed schema sql statements for postgress & sqlite

## Version 0.2.1-alpha

---

### New features:

* Setup model tests

### Improvements:

* initialize postgress & sql_light migrations

## Version 0.2.0-alpha

---

### New features:

* Save Editions global config option

### Improvements:

* code cleanup
* stable version, future ready for styling

### Bug fixes:

* properly restore 'content' from edition

## Version 0.1.9-beta

---

### New features:

* file attachment ui only, doesn't save

### Improvements:

* updated readme

## Version 0.1.8-beta

---

### New features:

* show editions listing
* restore edition

## Version 0.1.7-beta

---

### 1st stable version

### New features:

* full crud on wikipages
* wiki page ui
    * wiki overview page
    * detail pages
    * save/edit modals
* save editions config ui
* stores copies of pages as editions

### Improvements:

*

Bug fixes:

*
