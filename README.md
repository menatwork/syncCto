syncCto
=======

About
-----

syncCto provides the ability to synchronize multiple contao installations based on a basic installation. All actions can be conveniently carried out in the backend. By integrating into the contao permission system, editors can also synchronize a selection of predefined database tables and files.

An integrated backup manager backup selected database tables, either the entire contao installation or just the personal data. Backups can be imported again.

By using syncCto editors can work quickly and easily in a preview system, and then synchronize the current and approved version to the live system. Other possible applications might include:

* Preview <-> Live
* Development <-> Preview -> Live


Screenshot
-----------

![File overview](http://menatwork.github.io/sync-doku/screenshots/file-overview.png)


System requirements
-------------------

syncCto have a lot of system requirements, please check the system check at your contao backend.


Installation & Configuration
----------------------------

* Use !every! time the contao extension repository
* Install syncCto on your master installation
* Install syncCto on your client installation
* Get the api key from the contao settings at the client
* Create a new client entry at the master and fill out the mandatory fields
* Happy syncing


Troubleshooting
---------------

If you are having problems using syncCto, please visit the issue tracker at https://github.com/menatwork/syncCto/issues