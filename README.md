# AvantAdmin (plugin for Omeka Classic)

#################################################

> **This plugin is under development. Please wait for Release 2.0.**

#################################################

The AvantAdmin plugin customizes Omeka's admin interface and functionality to provide a simpler and more
efficient workflow for administrators. It is intended for Omeka installations that:

* Do not use Omeka collections
* Use the same Item Type for every item
 
The plugin hides the Omeka Collections and Item Type options thereby saving
the administrator from having to choose them each time they add a new item. This saves time and ensures that each
new item is added correctly.

AvantAdmin provides the following benefits:

* Hides the Omeka Collection feature.
* Hides the Omeka Item Type dropdown list from the Edit page and automatically assigns the same type to every item.
* Allows the administrator to configure the name for a single Item Type to be used for every item.
* Displays the admin Show page after a new item is added so that the admin can review the item to determine
 if further edits are needed. This overrides the normal Omeka behavior of displaying the
Browse Items page.
* Clearly indicates on the Show page if the item is not public (says the item is private). This helps to avoid the
problem of creating a new item and forgetting to make it public.
* Redirects Omeka simple keyword search requests to [AvantSearch](https://github.com/gsoules/AvantSearch) (only if AvantSearch is activated).
* Allows guest users having the Researcher role to see and search non-public items using the public interface.
Users with the Research role are prevented from accessing admin pages. On the public Show page, the field labels for
private items (specified using [AvantCommon](https://github.com/gsoules/AvantCommon)) are displayed in gray italics to indicate they are private.
* Displays an Item History on the admin Show page and lists the last five unique users that have saved the item. The
list is in reverse chronilogical order from newest to oldest.
* Overrides the Omeka Dashboard to display the last 100 modified and recently added items.
* Provides a down-for-maintenance feature that prevents public users from accessing the database, but still
allows administrators and developers to work on the site.

## Dependencies
The AvantAdmin plugin requires that the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin be installed and activated.

## Installation

To install the AvantAdmin plugin, follow these steps:

1. First install and activate the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin.
1. Unzip the AvantAdmin-master file into your Omeka installation's plugin directory.
1. Rename the folder to AvantAdmin.
1. Activate the plugin from the Admin → Settings → Plugins page.
1. On the AvantAdmin configuration page, specify the name of the Item Type to be used for all itmes.

## Usage
AvantAdmin has the following configuration options.

Option | Description
--------|------------
Maintenance |  When this option is checked, a "Down for maintenance" page will be displayed to public users to prevent them from accessing the site. Logged in users will not be affected.
Item&nbsp;Type&nbsp;Name | Specifies the name of the Item Type that will be used for every item in the installation.

## Warning

Use it at your own risk.

##  License

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Copyright
---------

* Created by [gsoules](https://github.com/gsoules) 
* Copyright George Soules, 2016-2018.
* See [LICENSE](https://github.com/gsoules/AvantAdmin/blob/master/LICENSE) for more information.

