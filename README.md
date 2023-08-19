# PlayerManager
A PocketMine-MP Plugin that allows admins to manage players and acquire players information using FormAPI!\
This plugin was inspired by [PlayerInfo](https://github.com/Matthww/PlayerInfo)

The player's information includes:
- Player's device model
- Player's device operating system
- Player's IP address
- Player's port
- Player's UI Scale
- Player's GUI Profile
- Player's UUID
- and more!

## What this plugin can do
- Getting player's information (basic)
- Kicking or banning player through Session category (done through commands)
- Allows player to toggle flight or no-clipping walls
- Editing player's attributes\
  It can be anything such as changing player's scale (size) or making the player invisible

## Commands
To use this plugin, you can use command `/plmanager`, and it will open the PlayerManager form!\
There are also command arguments that you can use to get into the action quickly!

Such as:
- `/plmanager info <player>` - Opening player's information
- `/plmanager session <player>` - Opening player's session
- `/plmanager ability <player>` - Opening player's ability
- `/plmanager attributes <player>` - Opening player's attributes
- `/plmanager reload` - Reloads PlayerManager's configuration file

You can change the `<player>` argument to any player name\
(You can use `@s` as the player argument to indicate you)\
(Example: `/plmanager info @s`)

## Permissions
- `playermanager.command.plmanager` - Allows user/player to open PlayerManager form

## Note
This is **my very first PocketMine-MP plugin**. Expect some bad or inefficient codes.\
Any suggestions, bug-reports, and pull-requests are allowed
