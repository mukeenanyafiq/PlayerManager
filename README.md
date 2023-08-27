# PlayerManager
A PocketMine-MP Plugin that allows player to manage players and acquire players information using FormAPI!\
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

## Purposes of this plugin
- Getting player's information (basic)
- Kicking or banning player through Session category (done through commands)
- Allows player to toggle flight or no-clipping walls
- Editing player's attributes\
  It can be anything such as changing player's scale (size) or making the player invisible

## Commands
| Commands     | Description                | Permissions                       |
|--------------|----------------------------|-----------------------------------|
| `/plmanager` | Opens a PlayerManager form | `playermanager.command.plmanager` |

| Arguments    | Description                                | Usages                           |
|--------------|--------------------------------------------|----------------------------------|
| `info`       | Opens player's information                 | `/plmanager info <player>`       |
| `session`    | Opens player's session                     | `/plmanager session <player>`    |
| `ability`    | Opens player's ability                     | `/plmanager ability <player>`    |
| `attributes` | Opens player's attributes                  | `/plmanager attributes <player>` |
| `effects`    | Opens player's effects                     | `/plmanager effects <player>`    |
| `reload`     | Reloads PlayerManager's configuration file | `/plmanager reload`              |

You can change the `<player>` argument to any online player name as it is required for the commands to work\
(You can use `@s` as the player argument to indicate you)\
(Example: `/plmanager info @s`)

## Suggestions & Bug Reports
If you have an idea of a new feature for this plugin, you can submit your idea [here!](https://github.com/mukeenanyafiq/PlayerManager/issues/new?assignees=&labels=suggestion&projects=&template=suggestion.md&title=Suggestion)

However, if you found or encounter any bugs in this plugin, you can submit your report [here!](https://github.com/mukeenanyafiq/PlayerManager/issues/new?assignees=&labels=bug&projects=&template=bug_report.md&title=Bug+Report)

## Note
This is **my very first PocketMine-MP plugin**. Expect some unorganized scripts, bad and inefficient codes.\
Any suggestions, bug-reports, and pull-requests are welcomed
