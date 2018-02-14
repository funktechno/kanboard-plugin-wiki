Wiki Plugin for Kanboard
==============================

Wiki to document projects

[![Build Status](https://travis-ci.org/kanboard/plugin-wiki.svg?branch=master)](https://travis-ci.org/kanboard/plugin-wiki)

- Create wiki lines
- See expenses based on sub-task time tracking
- Manage user hourly rates

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

### Wiki management

Wiki management is based on the sub-task time tracking, the user timetable and the user hourly rate.

This section is available from project settings page: **Project > Wiki**. There is also a shortcut from the drop-down menu on the board.

#### Wiki Lines

![Cost Lines](https://cloud.githubusercontent.com/assets/323546/20451620/965a4a2e-adc9-11e6-9131-3088ce6d8d78.png)

Wiki lines are used to define a wiki for the project.
This wiki can be adjusted by adding a new entry with an effective date.

#### Cost Breakdown

![Cost Breakdown](https://cloud.githubusercontent.com/assets/323546/20451619/9658c9ba-adc9-11e6-8dd9-97b7d01db7f2.png)

Based on the subtask time tracking table and user information you can see the cost of each subtask.

The time spent is rounded to the nearest quarter.

#### Wiki Chart

![Wiki Graph](https://cloud.githubusercontent.com/assets/323546/20451621/965c1110-adc9-11e6-925c-c37c5a738c26.png)

Finally, by combining all information we can generate a graph:

- Expenses represent user costs
- Wiki lines are the provisioned wiki
- Remaining is the wiki left at the given time

### Hourly Rate

Each user can have a pre-defined hourly rate.
This feature is used for wiki calculation.

To define a new price, go to **User profile > Hourly rates**.

![Hourly Rate](https://cloud.githubusercontent.com/assets/323546/20451622/965da606-adc9-11e6-9537-cd987abac06d.png)

Each hourly rate can have an effective date and different currencies.
