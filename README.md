# PlayerManager
A PocketMine-MP Plugin that allows admins to manage players and acquire players informations using FormAPI!\
This plugin was inspired by [PlayerInfo](https://github.com/Matthww/PlayerInfo)

The player's informations includes:
- Player's device model
- Player's device operating system
- Player's IP address
- Player's port
- Player's UI Scale
- Player's GUI Profile
- Player's UUID
- and more!

## Purposes of this plugin
- Getting player's informations (basic)
- Kicking or banning player through Session category (done through commands)
- Allows player to toggle flight or no-clipping walls
- Editing player's attributes\
  It can be anything such as changing player's scale (size) or making the player invisible

## Commands
To use this plugin, you can use command `/plmanager`, and it will open the PlayerManager form!\
There are also command arguments that you can use to get into the action quickly!

Such as:
- `/plmanager info <player>` - Opening player's informations
- `/plmanager session <player>` - Opening player's session
- `/plmanager ability <player>` - Opening player's ability
- `/plmanager attributes <player>` - Opening player's attributes
- `/plmanager effects <player>` - Opening player's effects
- `/plmanager reload` - Reloads PlayerManager's configuration file

Usages:
- `/plmanager [info|session|ability|attributes|effects] <player>`
- `/plmanager reload`

You can change the `<player>` argument to any online player name as it is required for the commands to work\
(You can use `@s` as the player argument to indicate you)\
(Example: `/plmanager info @s`)

## Permissions
- `playermanager.command.plmanager` - Allows user/player to open PlayerManager form

## Note
This is **my very first PocketMine-MP plugin**. Expect some bad or inefficient codes.\
Any suggestions, bug-reports, and pull-requests are accepted
