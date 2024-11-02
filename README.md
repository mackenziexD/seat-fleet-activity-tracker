> [!NOTE]  
> This package is no where near finished so dont use it.

# SeAT-Fleet-Activity-Tracker - Fleet Tracking plugin for SeAT
![](https://img.shields.io/github/v/release/mackenziexD/seat-fleet-activity-tracker?style=for-the-badge&label=VERSION&color=%252328a3df)
[![Total Downloads](https://img.shields.io/packagist/dt/helious/seat-fleet-activity-tracker.svg?style=for-the-badge)](https://packagist.org/packages/helious/seat-fleet-activity-tracker)

## Installation

### Important
FC's need scope `esi-fleets.read_fleet.v1` if they want to pap fleets. if you changed your Single Sign-on SSO scopes under settings you will need to add the scopes back and any characters that will be used to track fleets will have to be re-linked to update the tokens so they have the scopes needed.

#### Step 1: Install
You can install the package via composer:
```bash
composer require helious/seat-fleet-activity-tracker
```
or via docker
```bash
SEAT_PLUGINS=helious/seat-fleet-activity-tracker
```

#### Step 2: Schedule
Create a new schedule under `Settings > Schedule` and select `fats:update:fleets` running `every minute`. this will run the command that runs the job to pull fleet members from an active fleet.

### Permissions
| Name | Description |
| --- | --- |
| access | Permission to access the FATs. |
| track | Permission to start tracking a fleet. |
| allFleets | Permission to see all previous fatted fleets. |
| stats | Permission to see corp stats. |

### Todo
- [ ] Ajax for tracked fleets to autopull updates, saves having to refresh.
- [ ] Select corps to track paps for, currently the way its pulling is messy.
- [ ] Ability to delete ships from paps/papped fleets.
- [ ] Update corp motd when fleet is being tracked? maybe unsure yet.
- [ ] Tools to kick all members/pods from fleet
