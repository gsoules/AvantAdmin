# AvantAdmin (plugin for Omeka Classic)

The AvantAdmin plugin customizes Omeka's admin interface and functionality to provide a simpler and more
efficient workflow for administrators. It is intended for Omeka installations that do not use Omeka collections
and use the same Item Type for every item. The plugin hides the Omeka Collections and Item Type options thereby saving
the administrator from having to choose them each time they add a new item.

AvantAdmin provides the following benefits:

* Hides the Omeka Collection feature.
* Hides the Omeka Item Type dropdown list from the Edit page and automatically assigns the same type to every item.
* Allows the administrator to configure the name for a single Item Type to be used for every item.
* Allows users having the Researcher role to access non-public items using the public interface.
* Provides a down-for-maintenance feature that prevents public users from accessing the database, but still
allows administrators and developers to work on the site.

## Dependencies
The AvantAdmin plugin requires that the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin be installed and activated.

## Installation

To install the AvantADmin plugin, follow these steps:

1. First install and activate the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin.
1. Unzip the AvantAdmin-master file into your Omeka installation's plugin directory.
1. Rename the folder to AvantAdmin.
1. Activate the plugin from the Admin → Settings → Plugins page.
1. On the AvantAdmin configuration page, specify the name of the Item Type to be used for all itmes.

## Usage
* On the AvantAdmin configuration page, check the Maintance box when you want to temporarily prevent public users
from accessing the site.

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

