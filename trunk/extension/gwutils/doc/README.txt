Author
======
Bjørn Reiten


Description
===========

Contains a workflow to set the hidden field for content with the date fields
for publish and unpublish. The workflow will be runned at the last trigger of
the publish process.


Installation
============

1. Unpack the extension under the "extension" directory of your eZ publish
installation.

Log into the admin interface:
2. Add the extension gwutils in (/setup/extensions) or edit your
site.ini.append.php file and add:

   [ExtensionSettings]
   ActiveExtensions[]=gwutils

Clear cache.


Usage of the Update Hidden workflow
===================================

Create a new workflow which should update the hidden field when the content is
published. 

- Log into the admin interface and create a workflow in a workflow group. 
- Add the event "Event / Update hidden field".
- Select the attributes that should be connected to when an object should be
  published and when the object should be unpublished. The "Modify publish
  date" will modify the publish date for the object, when the content is
  published into the future.
- Store the event and add the workflow to the trigger:
  content     publish      after

When you create new content and when the datefield are added in the workflow,
the content will be set to hidden when the date is set to the future. Otherwise
it will be set to visible.

When the unpublish date is set in the future, the content will set to hidden by
a cronjob.


Set up cronjobs
===============

The Update Hidden workflow are using the normal workflow cronjob to run the
cron update script. This cronjob are added to the group frequent by default.

The script can be runned by:
php runcronjobs.php frequent

it is also possible to add the workflow.php cronjob to a different group, if
you want that. Example in cronjob.ini.append.php:

[CronjobPart-updatehidden]
Scripts[]=workflow.php

Where you can run this command instead:
php runcronjobs.php updatehidden


Requirements
============

eZ publish 4+.
 
