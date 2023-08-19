<?php

declare(strict_types=1);

namespace mukeenanyafiq\PlayerManager;

/* These forms class are originally from jojoe77777\FormAPI */
use mukeenanyafiq\FormAPI\SimpleForm;
use mukeenanyafiq\FormAPI\CustomForm;
use mukeenanyafiq\FormAPI\ModalForm;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener {
    public const FIRST_PLACE_TYPE = [
        "model", 
        "os", 
        "ip", 
        "port", 
        "ping", 
        "ui", 
        "gui", 
        "controls", 
        "uuid", 
        "health", 
        "position", 
        "gamemode"
    ];

    public const FIRST_PLACE_TYPE_EXTRA = [
        "model" => 0, 
        "os" => 1, 
        "ip" => 2, 
        "port" => 3, 
        "ping" => 4, 
        "ui" => 5, 
        "gui" => 6, 
        "controls" => 7, 
        "uuid" => 8, 
        "health" => 9, 
        "position" => 10,
        "gamemode" => 11
    ];

    public const PLAYER_MANAGE_CATEGORY = [
        "session",
        "ability",
        "attributes"
    ];

    public const AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER = [
        "info",
        "reload",
        "session",
        "ability",
        "attributes"
    ];

    public $firstplacetypechoosen = "";

    private const FORMTITLE = "PlayerManager";

    private const SUPPORTED_LANGUAGE_LIST = [
        "eng"
    ];

    private $pluginlanguage;

    public function onLoad(): void {
        $this->getLogger()->info("Plugin loaded");
    }

    public function onEnable(): void {
        $this->getLogger()->info("Plugin has been enabled");
        if (in_array($this->getConfig()->get("firstplace"), $this::FIRST_PLACE_TYPE) === false) {
            $this->getLogger()->warning("Unknown firstplace type: '" .strtolower($this->getConfig()->get("firstplace")). "'. Check any misspells or typos you might have made. For now, the type will be set to 'model' for default");
            $this->firstplacetypechoosen = "model";
        } else {
            $this->firstplacetypechoosen = $this->getConfig()->get("firstplace");
        }
        if (count($this->getConfig()->get("blacklist")) > 0) {
            if (count($this->getConfig()->get("blacklist")) === 1) {
                $this->getLogger()->info("There are " .count($this->getConfig()->get("blacklist")). " blacklisted player from using PlayerManager form: " .implode(", ", $this->getConfig()->get("blacklist")));
            } else {
                $this->getLogger()->info("There are " .count($this->getConfig()->get("blacklist")). " blacklisted players from using PlayerManager form: " .implode(", ", $this->getConfig()->get("blacklist")));
            }
        }
        if (in_array($this->getConfig()->get("language"), $this::SUPPORTED_LANGUAGE_LIST)) {
            $this->pluginlanguage = parse_ini_file($this->getDataFolder(). "lang/" .$this->getConfig()->get("language"). ".ini", false, INI_SCANNER_RAW);
        } else {
            $this->getLogger()->warning("Unknown language: '" .strtolower($this->getConfig()->get("language")). "'. Check any misspells or typos you might have made. For now, the language will be set to 'eng' for English as default");
            $this->pluginlanguage = parse_ini_file($this->getDataFolder(). "lang/eng.ini", false, INI_SCANNER_RAW);
        }
    }

    public function getLanguageString(string $string) {
        return $this->pluginlanguage[$string];
    }

    public function onCommand(CommandSender $commandSender, Command $command, string $commandLabel, array $args): bool {
        switch ($command->getName()) {
            case "plmanager":
                if (!$commandSender->hasPermission("playermanager.command.plmanager")) {
                    $commandSender->sendMessage("This command is only intended for operators!");
                    return true;
                }

                if (isset($args[0])) {
                    if (!in_array($args[0], $this::AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER)) {
                        if ($commandSender instanceof Player) {
                            $commandSender->sendMessage(TF::colorize("&cERROR: Invalid argument '" .$args[0]. "'. If you want to open player information, you may aswell try /plmanager info <player>"));
                            $commandSender->sendMessage("Available argument: " .implode(", ", $this::AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER));
                            return true;
                        } else {
                            $commandSender->sendMessage(TF::colorize("&cERROR: Invalid argument '" .$args[0]. "'."));
                            $commandSender->sendMessage("Available argument ('reload' is the only argument that is usable from everywhere): " .implode(", ", $this::AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER));
                            return true;
                        }
                    }

                    if ($args[0] === "reload") {
                        $this->getConfig()->reload();
                        $commandSender->sendMessage(TF::colorize("&aConfiguration file (config.yml) has been reloaded."));
                        $this->getLogger()->info("Configuration file (located in " .$this->getDataFolder(). "config.yml) has been reloaded by " .$commandSender->getName(). ".");
                        if (in_array($this->getConfig()->get("firstplace"), $this::FIRST_PLACE_TYPE) === false) {
                            $commandSender->sendMessage(TF::colorize("&eUnknown firstplace type: '" .strtolower($this->getConfig()->get("firstplace")). "'. Check any misspells or typos you might have made. For now, the type will be set to 'model' for default"));
                            $this->firstplacetypechoosen = "model";
                        } else {
                            $this->firstplacetypechoosen = $this->getConfig()->get("firstplace");
                        } 
                        if (count($this->getConfig()->get("blacklist")) > 0) {
                            if (count($this->getConfig()->get("blacklist")) === 1) {
                                $this->getLogger()->info("There are " .count($this->getConfig()->get("blacklist")). " blacklisted player from using PlayerManager form: " .implode(", ", $this->getConfig()->get("blacklist")));
                            } else {
                                $this->getLogger()->info("There are " .count($this->getConfig()->get("blacklist")). " blacklisted players from using PlayerManager form: " .implode(", ", $this->getConfig()->get("blacklist")));
                            }
                        }
                        if (in_array($commandSender->getName(), $this->getConfig()->get("blacklist"))) {
                            if ($commandSender->getName() === "CONSOLE") {
                                $commandSender->sendMessage(TF::colorize("&cIt's useless to blacklist Console. Console can't open forms."));
                            } else {
                                $commandSender->sendMessage(TF::colorize("&cUnfortunately, you are in the list of blacklisted players from using PlayerManager form. This means &lyou can't use PlayerManager form anymore."));
                            }
                        }
                        if (in_array($this->getConfig()->get("language"), $this::SUPPORTED_LANGUAGE_LIST)) {
                            $this->pluginlanguage = parse_ini_file($this->getDataFolder(). "lang/" .$this->getConfig()->get("language"). ".ini", false, INI_SCANNER_RAW);
                        } else {
                            $this->getLogger()->warning("Unknown language: '" .strtolower($this->getConfig()->get("language")). "'. Check any misspells or typos you might have made. For now, the language will be set to 'eng' for English as default");
                            $this->pluginlanguage = parse_ini_file($this->getDataFolder(). "lang/eng.ini", false, INI_SCANNER_RAW);
                        }
                        return true;
                    }
                }

                if ($commandSender instanceof Player) {
                    if (in_array($commandSender->getName(), $this->getConfig()->get("blacklist"))) {
                        $commandSender->sendMessage(TF::colorize($this->getLanguageString("&cSorry, you are not allowed to use this command. Even though you have the permission to use the command, you're on the list of the blacklisted players. If you have the access to the server's files, you can delete your username off from the list in the configuration file.")));
                        return true;
                    }

                    $this->openPlayerManagerForm($commandSender, $args);
                } else {
                    $commandSender->sendMessage("This command only works for players! Forcing to execute on console might occurs an error");
                }
                return true;
        }
        return true;
    }

    public function openPlayerManagerForm($player, $args) {
        $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "Playstation 4", "Nintento Switch", "Xbox One"];
        $UI = ["Classic UI", "Pocket UI"];
        $Controls = ["Unknown", "Mouse", "Touch", "Controller"];
        $GUI = [-2 => "Minimum", -1 => "Medium", 0 => "Maximum"];

        $currpl = $this->getServer()->getPluginManager()->getPlugin("PlayerManager");
        if (isset($args[0])) {
            switch ($args[0]) {
                case "info":
                    if (!isset($args[1])) {
                        $player->sendMessage(TF::colorize("&cERROR: No player name was put. Please put the player's name! (TIP: you can use @s to indicate yours)"));
                        return false;
                    }
                    
                    if ($args[1] === "@s") {
                        $this->openPlayerInformationPage($player, $player->getName());
                        return true;
                    }

                    if (!$this->getServer()->getPlayerExact($args[1])) {
                        $player->sendMessage(TF::colorize("&cERROR: No player with the name &f" .$args[0]. "&c is online"));
                        return false;
                    }
                    
                    $this->openPlayerInformationPage($player, $args[1]);
                break;
                
                case $args[0]:
                    if (!in_array($args[0], $this::AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER)) {
                        $player->sendMessage(TF::colorize("&cERROR: Invalid argument '" .$args[0]. "'. If you want to open player information, you may aswell try /plmanager info <player>"));
                        $player->sendMessage("Available argument: " .implode(", ", $this::AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER));
                    }

                    if (!isset($args[1])) {
                        $player->sendMessage(TF::colorize("&cERROR: No player name was put. Please put the player's name! (TIP: you can use @s to indicate yours)"));
                        return false;
                    }
    
                    if ($args[1] === "@s") {
                        $this->openPlayerManageCategory($player, $args[0], $player->getName());
                        return true;
                    }

                    if (!$this->getServer()->getPlayerExact($args[1])) {
                        $player->sendMessage(TF::colorize("&cERROR: No player with the name &f" .$args[0]. "&c is online"));
                        return false;
                    }
    
                    $this->openPlayerManageCategory($player, $args[0], $args[1]);
                break;
            }
        } else {
            $form = new SimpleForm(function (Player $player, $data = null) use ($os, $UI, $Controls, $GUI) {
                if ($data === null) {
                    return true;
                }

                switch ($data) {
                    case 0:
                        $form = new SimpleForm(function (Player $player, $data = null) {
                            if ($data === null) {
                                return true;
                            }
                            
                            switch ($data) {
                                case $data:
                                    $target = array_values($this->getServer()->getOnlinePlayers())[$data]->getName();
                                    $this->openPlayerInformationPage($player, $target);
                            }
                        });
                        $form->setTitle($this::FORMTITLE);
                        $form->setContent("Select the player you want to manage");
                        foreach ($this->getServer()->getOnlinePlayers() as $value) {
                            $clientData = $value->getPlayerInfo()->getExtraData();

                            switch (strtolower($this->firstplacetypechoosen)) {
                                case "model":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$clientData["DeviceModel"]));
                                case "os":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$os[$clientData["DeviceOS"]]));
                                case "ip":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getNetworkSession()->getIp()));
                                case "port":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getNetworkSession()->getPort()));
                                case "ping":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getNetworkSession()->getPing(). 'ms'));
                                case "ui":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$UI[$clientData["UIProfile"]]));
                                case "gui":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$GUI[$clientData["GuiScale"]]));
                                case "controls":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$Controls[$clientData["CurrentInputMode"]]));
                                case "uuid":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getUniqueId()));
                                case "health":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getHealth(). " HP"));
                                case "position":
                                    $form->addButton(TF::colorize($value->getName(). "\n&lX: " .$value->getPosition()->getFloorX(). " Y: " .$value->getPosition()->getFloorY(). " Z: " .$value->getPosition()->getFloorZ()));
                                case "gamemode":
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getGamemode()->getTranslatableName()->getText()));
                            }
                        }
                        $player->sendForm($form);
                        return $form;
                    case 1:
                        $form = new SimpleForm(function (Player $player, $data = null) {
                            if ($data === null) {
                                return true;
                            }
                            
                            switch ($data) {
                                case 0:
                                    $form = new SimpleForm(function (Player $player, $data = null) {
                                        if ($data === null) {
                                            return true;
                                        }
                                        
                                        switch ($data) {
                                            case $data:
                                                $target = $this->getServer()->getOfflinePlayer(array_values($this->getServer()->getNameBans()->getEntries())[$data]->getName());
                                                $form = new SimpleForm(function (Player $player, $data = null) use ($target) {
                                                    if ($data === null) {
                                                        return true;
                                                    }
                                                    
                                                    switch ($data) {
                                                        case 0:
                                                            $form = new ModalForm(function (Player $player, $data = null) use ($target) {
                                                                if ($data === null) {
                                                                    return true;
                                                                }
            
                                                                switch ($data) {
                                                                    case 0:
                                                                        $player->sendMessage("The action has been canceled.");
                                                                    break;
            
                                                                    case 1:
                                                                        $this->getServer()->getNameBans()->remove($target->getName());
                                                                        $this->getLogger()->info($target->getName(). " has been unbanned");
                                                                        $player->sendMessage(TF::colorize("&aSuccessfully unbanned &f" .$target->getName(). "&a but still.. be careful."));
                                                                    break;
                                                                }
                                                            });
                                                            $form->setTitle($this::FORMTITLE. " - Unban player");
                                                            $form->setContent(TF::colorize("Are you sure you want to unban &a" .$target->getName(). "&f? The player might break another rule of the server again after the player has been unbanned. If you think the player is innocent enough, you may unban the player."));
                                                            $form->setButton1("Yes");
                                                            $form->setButton2("No");
                                                            $player->sendForm($form);
                                                            return $form;
                                                    }
                                                });
                                                $form->setTitle($target->getName());
                                                if (strtolower($target->getName()) === strtolower($this->getServer()->getNameBans()->getEntry($target->getName())->getSource())) {
                                                    $form->setContent(TF::colorize("This player was banned by &lHIMSELF?&r\n(kind of weird)\n \nReason: " .$this->getServer()->getNameBans()->getEntry($target->getName())->getReason(). "\n \nWhat do you want to do with &a" .$target->getName(). "&f?"));
                                                } elseif ($this->getServer()->getNameBans()->getEntry($target->getName())->getSource() === "CONSOLE") {
                                                    $form->setContent(TF::colorize("Someone banned him using &lConsole&f.\n(how are we supposed to know the player that banned him using console)\n \nReason: " .$this->getServer()->getNameBans()->getEntry($target->getName())->getReason(). "\n \nWhat do you want to do with &a" .$target->getName(). "&f?"));
                                                } else {
                                                    $form->setContent(TF::colorize("This player was banned by: ".$this->getServer()->getNameBans()->getEntry($target->getName())->getSource(). "\nReason: " .$this->getServer()->getNameBans()->getEntry($target->getName())->getReason(). "\n \nWhat do you want to do with &a" .$target->getName(). "&f?"));
                                                }
                                                $form->addButton(TF::colorize("Unban (/unban or /pardon)\n&lUnban the player"));
                                                $player->sendForm($form);
                                                return $form;
                                        }
                                    });
                                    $form->setTitle($this::FORMTITLE);
                                    $form->setContent("Select one of the banned players you want to manage");
                                    foreach ($this->getServer()->getNameBans()->getEntries() as $value) {
                                        $form->addButton(TF::colorize($this->getServer()->getOfflinePlayer($value->getName())->getName(). "\n&l" .$value->getReason()));
                                    }
                                    $player->sendForm($form);
                                    return $form;
                                case 1:
                                    $form = new SimpleForm(function (Player $player, $data = null) {
                                        if ($data === null) {
                                            return true;
                                        }
                                        
                                        switch ($data) {
                                            case $data:
                                                $target = array_values($this->getServer()->getIPBans()->getEntries())[$data];
                                                $form = new SimpleForm(function (Player $player, $data = null) use ($target) {
                                                    if ($data === null) {
                                                        return true;
                                                    }
                                                    
                                                    switch ($data) {
                                                        case 0:
                                                            $form = new ModalForm(function (Player $player, $data = null) use ($target) {
                                                                if ($data === null) {
                                                                    return true;
                                                                }
            
                                                                switch ($data) {
                                                                    case 0:
                                                                        $player->sendMessage("The action has been canceled.");
                                                                    break;
            
                                                                    case 1:
                                                                        $this->getServer()->getIPBans()->remove($target->getName());
                                                                        $this->getLogger()->info("IP ".$target->getName(). " has been unbanned");
                                                                        $player->sendMessage(TF::colorize("&aSuccessfully unbanned IP &f" .$target->getName(). "&a but still.. be careful."));
                                                                    break;
                                                                }
                                                            });
                                                            $form->setTitle($this::FORMTITLE. " - Unban IP");
                                                            $form->setContent(TF::colorize("Are you sure you want to unban IP &a" .$target->getName(). "&f? The player with the IP might break another rule of the server again after the player has been unbanned. If you think the player is innocent enough, you may unban the player with the IP."));
                                                            $form->setButton1("Yes");
                                                            $form->setButton2("No");
                                                            $player->sendForm($form);
                                                            return $form;
                                                    }
                                                });
                                                $form->setTitle($target->getName());
                                                $form->setContent(TF::colorize("This IP was banned by: ".$target->getSource(). "\nReason: " .$target->getReason(). "\n \nWhat do you want to do with the IP &a" .$target->getName(). "&f?"));
                                                $form->addButton(TF::colorize("Unban IP (/unban-IP or /pardon-IP)\n&lUnban the IP"));
                                                $player->sendForm($form);
                                                return $form;
                                        }
                                    });
                                    $form->setTitle($this::FORMTITLE);
                                    $form->setContent("Select one of the banned IP you want to manage");
                                    foreach ($this->getServer()->getIPBans()->getEntries() as $value) {
                                        $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getReason()));
                                    }
                                    $player->sendForm($form);
                                    return $form;
                            }
                        });
                        $form->setTitle($this::FORMTITLE);
                        $form->setContent("Select a banned player type");
                        $form->addButton("Banned players Name");
                        $form->addButton("Banned players IP");
                        $player->sendForm($form);
                        return $form;
                    case 2:
                        $this->getConfig()->reload();
                        $player->sendMessage(TF::colorize("&aConfiguration file (config.yml) has been reloaded."));
                        $this->getLogger()->info("Configuration file (located in " .$this->getDataFolder(). "config.yml) has been reloaded by " .$player->getName(). ".");
                        if (in_array($this->getConfig()->get("firstplace"), $this::FIRST_PLACE_TYPE) === false) {
                            $player->sendMessage(TF::colorize("&eUnknown firstplace type: '" .strtolower($this->getConfig()->get("firstplace")). "'. Check any misspells or typos you might have made. For now, the type will be set to 'model' for default"));
                            $this->firstplacetypechoosen = "model";
                        } else {
                            $this->firstplacetypechoosen = $this->getConfig()->get("firstplace");
                        }
                        if (in_array($player->getName(), $this->getConfig()->get("blacklist"))) {
                            $player->sendMessage(TF::colorize("&cUnfortunately, you are in the list of blacklisted players from using PlayerManager form. This means &lyou can't use PlayerManager form anymore."));
                        }
                    break;
                }
            });
            $form->setTitle($this::FORMTITLE);
            $form->setContent("PlayerManager v" .$currpl->getDescription()->getVersion(). "\nCreated by " .array_values($currpl->getDescription()->getAuthors())[0]. "\n \nSelect an action to continue");
            $form->addButton("Manage a player\n" .TF::BOLD. "Managing a player");
            $form->addButton("Manage a banned player\n" .TF::BOLD. "Managing a banned player");
            $form->addButton("Reload\n" .TF::BOLD. "Reload configuration");
            $player->sendForm($form);
            return $form;
        }
    }

    public function openPlayerInformationPage($player, $target) {
        $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "Playstation 4", "Nintento Switch", "Xbox One"];
        $UI = ["Classic UI", "Pocket UI"];
        $Controls = ["Unknown", "Mouse", "Touch", "Controller"];
        $GUI = [-2 => "Minimum", -1 => "Medium", 0 => "Maximum"];

        $playerchoosen = $this->getServer()->getPlayerExact($target);
        $clientdata = $playerchoosen->getPlayerInfo()->getExtraData();

        $form = new SimpleForm(function (Player $player, $data = null) use ($target) {
            $playertarget = $this->getServer()->getPlayerExact($target);
            if ($data === null) {
                return true;
            }

            switch ($data) {
                case 0:
                    $this->openPlayerManageCategory($player, "session", $target);
                break;

                case 1:
                    $this->openPlayerManageCategory($player, "ability", $target);
                break;
                
                case 2:
                    $this->openPlayerManageCategory($player, "attributes", $target);
                break;
            }
        });
        $form->setTitle($playerchoosen->getName());
        $form->setContent(TF::colorize("&aDisplay Name: &f" .$playerchoosen->getDisplayName(). "\n&aModel: &f" .$clientdata["DeviceModel"]. "\n&aOS: &f" .$os[$clientdata["DeviceOS"]]. "\n&aIP: &f" .$playerchoosen->getNetworkSession()->getIp(). "\n&aPort: &f" .$playerchoosen->getNetworkSession()->getPort(). "\n&aPing: &f" .$playerchoosen->getNetworkSession()->getPing(). "ms\n&aUI: &f" .$UI[$clientdata["UIProfile"]]. "\n&aGUI Scale: &f" .$GUI[$clientdata["GuiScale"]]. "\n&aControls: &f" .$Controls[$clientdata["CurrentInputMode"]]. "\n&aUUID: &f" .$playerchoosen->getUniqueId(). "\n&aHealth: &f" .$playerchoosen->getHealth(). " HP\n&aPosition: &fX: " .$playerchoosen->getPosition()->getFloorX() . ", Y: " .$playerchoosen->getPosition()->getFloorY() . ", Z: " .$playerchoosen->getPosition()->getFloorZ(). "\n&aGamemode: &f" .$playerchoosen->getGamemode()->name()));
        $form->addButton(TF::colorize("Session\n&lPlayer's session"));
        $form->addButton(TF::colorize("Ability\n&lPlayer's ability"));
        $form->addButton(TF::colorize("Attributes\n&lPlayer's attributes"));
        $player->sendForm($form);
        return $form;
    }

    public function openPlayerManageCategory($player, string $category, $target) {
        $playertarget = $this->getServer()->getPlayerExact($target);
        switch ($category) {
            case "session":
                $form = new SimpleForm(function (Player $player, $data = null) use ($playertarget) {
                    if ($data === null) {
                        return true;
                    }
    
                    switch ($data) {
                        case 0:
                            $form = new CustomForm(function (Player $player, $data = null) use ($playertarget) {
                                if ($data === null) {
                                    return true;
                                }
    
                                if ($data[1] === true) {
                                    $player->sendMessage(TF::colorize("&aSuccessfully kicked &f" .$playertarget->getName(). "&a off from the server for the reason: " .$data[0]));
                                    $playertarget->kick("Kicked by admin. Reason: " .$data[0], null, "$data[0]");
                                } else {
                                    if ($data[0] === null) {
                                        $player->sendMessage(TF::colorize("&aSuccessfully kicked &f" .$playertarget->getName(). "&a off from the server for the reason: " .$data[0]));
                                        $playertarget->kick("Kicked by admin. Reason: " .$data[0], null, "Kicked by admin. Reason: " .$data[0]);
                                    } else {
                                        $player->sendMessage(TF::colorize("&aSuccessfully kicked &f" .$playertarget->getName(). "&a off from the server"));
                                        $playertarget->kick("Kicked by admin", null, "Kicked by admin");
                                    }
                                }
                            });
                            $form->setTitle("Kick");
                            $form->addInput(TF::colorize("You are about to kick &a" .$playertarget->getName(). "&f off from the server. Enter the reason why the player got kicked"), "Kick reason (optional)");
                            $form->addToggle(TF::colorize("Only shows the reason in the &a" .$playertarget->getName(). "&f's disconnected screen"));
                            $player->sendForm($form);
                            return $form;
                        case 1:
                            $form = new CustomForm(function (Player $player, $data = null) use ($playertarget) {
                                if ($data === null) {
                                    return true;
                                }
    
                                $this->getServer()->getNameBans()->addBan($playertarget->getName(), $data[0], null, $player->getName());
                                if ($data[0] === null) {
                                    $player->sendMessage(TF::colorize("&aSuccessfully banned &f" .$playertarget->getName(). "&a off from the server"));
                                    $playertarget->kick("You are banned from this server", null, "You are banned from this server");
                                } else {  
                                    $player->sendMessage(TF::colorize("&aSuccessfully banned &f" .$playertarget->getName(). "&a off from the server for the reason: " .$data[0]));
                                    $playertarget->kick("You are banned from this server. Reason: " .$data[0], null, "You are banned from this server. Reason: " .$data[0]);
                                }
                            });
                            $form->setTitle("Ban");
                            if ($playertarget->getName() === $player->getName()) {
                                $form->addInput(TF::colorize("WARNING! You are about to ban yourself off from the server. This means that you will not be able to join the server again. Enter the reason why you got banned"), "Ban reason (optional)");
                            } else {
                                $form->addInput(TF::colorize("You are about to ban &a" .$playertarget->getName(). "&f off from the server. Enter the reason why the player got banned"), "Ban reason (optional)");
                            }
                            $form->addInput("Put how long will the ban lasts in seconds", "Ban lasts in seconds (optional)");
                            $player->sendForm($form);
                            return $form;
                        case 2:
                            $form = new CustomForm(function (Player $player, $data = null) use ($playertarget) {
                                if ($data === null) {
                                    return true;
                                }
    
                                $this->getServer()->getIPBans()->addBan($playertarget->getNetworkSession()->getIp(), $data[1], null, $player->getName());
                                if ($data[1] === null) {
                                    $player->sendMessage(TF::colorize("&aSuccessfully IP-ban &f" .$playertarget->getName(). "&a off from the server"));
                                    $playertarget->kick("You are banned from this server. Reason: IP banned", null, "You are banned from this server. Reason: IP banned");
                                } else {
                                    $player->sendMessage(TF::colorize("&aSuccessfully IP-ban &f" .$playertarget->getName(). "&a off from the server for the reason: " .$data[1]));
                                    $playertarget->kick("You are banned from this server. Reason: " .$data[1], null, "You are banned from this server. Reason: " .$data[1]);
                                }
                            });
                            $form->setTitle("Ban IP");
                            if ($playertarget->getName() === $player->getName()) {
                                $form->addLabel(TF::colorize("NOTE: IP-banning &a" .$playertarget->getName(). "&f will only ban the player's IP and their IP could change anytime! We recommend you to use 'Ban' option instead"));
                                $form->addInput(TF::colorize("WARNING! You are about to IP-ban yourself off from the server. This means that you will not be able to join the server again unless your IP changed. Enter the reason why you got IP-banned"), "Ban reason (optional)");
                            } else {
                                $form->addLabel(TF::colorize("NOTE: IP-banning &a" .$playertarget->getName(). "&f will only ban the player's IP and their IP could change anytime! We recommend you to use 'Ban' option instead"));
                                $form->addInput(TF::colorize("You are about to IP-ban &a" .$playertarget->getName(). "&f off from the server. Enter the reason why the player got IP-banned"), "Ban reason (optional)");
                            }
                            $player->sendForm($form);
                            return $form;
                    }
                });
                $form->setTitle($playertarget->getName(). "'s Session");
                $form->setContent("Select an action to continue");
                $form->addButton(TF::colorize("Kick (/kick)\n&lKick the player"));
                $form->addButton(TF::colorize("Ban (/ban)\n&lBan the player"));
                $form->addButton(TF::colorize("Ban IP (/ban-ip)\n&lBan IP the player"));
                $player->sendForm($form);
                return $form;
            case "ability":
                $form = new SimpleForm(function (Player $player, $data = null) use ($playertarget) {
                    if ($data === null) {
                        return true;
                    }

                    switch ($data) {
                        case 0:
                            if ($playertarget->isCreative()) {
                                $player->sendMessage(TF::colorize($playertarget->getName(). " is on Creative. They could toggle their fly anytime they want."));
                            } else {
                                $playertarget->setAllowFlight(true);
                                $player->sendMessage(TF::colorize("&aFlying ability for " .$playertarget->getName(). " has been enabled! (toggle to fly)"));
                            }
                        break;

                        case 1:
                            if ($playertarget->hasBlockCollision() === false) {
                                $player->sendMessage(TF::colorize($playertarget->getName(). " already has No-Clip enabled!"));
                            } else {
                                $playertarget->setHasBlockCollision(false);
                                $player->sendMessage(TF::colorize("&aNo-Clip ability for " .$playertarget->getName(). " has been enabled!"));
                            }
                        break;

                        case 2:
                            if ($playertarget->isCreative()) {
                                $player->sendMessage(TF::colorize($playertarget->getName(). " is on Creative. They could toggle their fly anytime they want."));
                            } else {
                                $playertarget->setAllowFlight(false);
                                $playertarget->setFlying(false);
                                $player->sendMessage(TF::colorize("&aFlying ability for " .$playertarget->getName(). " has been disabled!"));
                            }
                        break;
                        
                        case 3:
                            if ($playertarget->hasBlockCollision() === true) {
                                $player->sendMessage(TF::colorize($playertarget->getName(). " already has No-Clip disabled!"));
                            } else {
                                $playertarget->setHasBlockCollision(true);
                                $player->sendMessage(TF::colorize("&aNo-Clip ability for " .$playertarget->getName(). " has been disabled!"));
                            }
                        break;
                    }
                });
                $form->setTitle($playertarget->getName(). "'s Ability");
                $form->setContent("Select an action to continue");
                $form->addButton(TF::colorize("Enable Flight\n&lAllows player to fly"));
                $form->addButton(TF::colorize("Enable No-Clip\n&lAllows player to no-clip"));
                $form->addButton(TF::colorize("Disable Flight\n&lDisable ability fly"));
                $form->addButton(TF::colorize("Disable No-Clip\n&lDisable ability no-clip"));
                $player->sendForm($form);
                return $form;
            case "attributes":
                $gamemodes = [
                    0 => "adventure", 
                    1 => "survival", 
                    2 => "creative", 
                    3 => "spectator"
                ];

                $gamemodes2 = array_flip($gamemodes);

                $max = 300;

                $form = new SimpleForm(function (Player $player, $data = null) use ($playertarget, $gamemodes, $gamemodes2, $max) {
                    if ($data === null) {
                        return null;
                    }

                    switch ($data) {
                        case 0:
                            $player->sendMessage($playertarget->getName(). "'s attributes - " .$this::FORMTITLE);
                            $player->sendMessage("Absorption: " .$playertarget->getAbsorption(). "\nAir Supply Ticks: " .$playertarget->getAirSupplyTicks(). "\nHas Auto Jump: " .$playertarget->hasAutoJump(). "\nIs Breathing: " .$playertarget->isBreathing(). "\nCan Climb: " .$playertarget->canClimb(). "\nCan Climb Walls: " .$playertarget->canClimbWalls(). "\nDisplay Name: " .$playertarget->getDisplayName(). "\nFire Ticks: " .$playertarget->getFireTicks(). "\nGamemode: " .$playertarget->getGamemode()->name(). "\nIs Gliding: " .$playertarget->isGliding(). "\nGravity: " .$playertarget->getGravity(). "\nHas Gravity: " .$playertarget->hasGravity(). "Health: " .$playertarget->getHealth(). " HP\nIs Invisible: " .$playertarget->isInvisible(). "\nMaximum Air Supply Ticks: " .$playertarget->getMaxAirSupplyTicks(). "\nMaximum Health: " .$playertarget->getMaxHealth(). "\nMovement Speed: " .$playertarget->getMovementSpeed(). "\nName Tag: " .$playertarget->getNameTag(). "\nIs Name Tag Always Visible: " .$playertarget->isNameTagAlwaysVisible(). "\nIs Name Tag Visible: " .$playertarget->isNameTagVisible(). "\nOn Fire For: " .$playertarget->isOnFire(). " seconds\nScale: " .$playertarget->getScale(). "\nIs Silent: " .$playertarget->isSilent(). "\nIs Sneaking: " .$playertarget->isSneaking(). "\nIs Sprinting: " .$playertarget->isSprinting(). "\nIs Swimming: " .$playertarget->isSwimming());
                        case 1:
                            $form = new CustomForm(function (Player $player, $data = null) use ($playertarget, $gamemodes) {
                                if ($data === null) {
                                    return true;
                                }
        
                                $form = new ModalForm(function (Player $player, $d2 = null) use ($playertarget, $gamemodes, $data) {
                                    if ($d2 === null) {
                                        return true;
                                    }
        
                                    switch ($d2) {
                                        case 0:
                                            $player->sendMessage("The action has been canceled.");
                                        break;
        
                                        case 1:
                                            $playertarget->setAbsorption(floatval($data[0]));
                                            $playertarget->setAirSupplyTicks(intval($data[1]));
                                            $playertarget->setAutoJump($data[2]);
                                            $playertarget->setBreathing($data[3]);
                                            $playertarget->setCanClimb($data[4]);
                                            $playertarget->setCanClimbWalls($data[5]);
                                            $playertarget->setDisplayName($data[6]);
                                            $playertarget->setFireTicks($data[7]);
                                            $playertarget->setGamemode(GameMode::fromString($gamemodes[$data[8]]));
                                            $playertarget->setGliding($data[9]);
                                            $playertarget->setGravity(floatval($data[10]));
                                            $playertarget->setHasGravity($data[11]);
                                            $playertarget->setHealth(floatval($data[12]));
                                            $playertarget->setInvisible($data[13]);
                                            $playertarget->setMaxAirSupplyTicks($data[14]);
                                            $playertarget->setMaxHealth($data[15]);
                                            $playertarget->setMovementSpeed(floatval($data[16]));
                                            $playertarget->setNameTag($data[17]);
                                            $playertarget->setNameTagAlwaysVisible($data[18]);
                                            $playertarget->setNameTagVisible($data[19]);
                                            $playertarget->setOnFire($data[20]);
                                            $playertarget->setScale(floatval($data[21]));
                                            $playertarget->setSilent($data[22]);
                                            $playertarget->setSneaking($data[23]);
                                            $playertarget->setSprinting($data[24]);
                                            $playertarget->setSwimming($data[25]);
                                            $player->sendMessage(TF::colorize("&a" .$playertarget->getName(). "'s attributes successfully changed!"));
                                        break;
                                    }
                                });
                                $form->setTitle($this::FORMTITLE. " - Confirmation");
                                if ($playertarget->getName() === $player->getName()) {
                                    $form->setContent("Are you sure you want to keep changing your attribute? This change could be a mess and there is no way to revert it back unless you remembered/saved the attributes!");
                                } else {
                                    $form->setContent("Are you sure you want to keep changing " .$playertarget->getName(). "'s attribute? This change could be a mess and there is no way to revert it back unless you remembered/saved the attributes!");
                                }
                                $form->setButton1("Yes");
                                $form->setButton2("No");
                                $player->sendForm($form);
                            });
                            $form->setTitle($playertarget->getName(). "'s Attributes");
                            $form->addSlider("Set player's absorption", 0, $max, 1, intval($playertarget->getAbsorption()));
                            $form->addSlider("Set player's air supply ticks", 0, $playertarget->getMaxAirSupplyTicks(), 1, $playertarget->getAirSupplyTicks());
                            $form->addToggle("Set player's autojump", $playertarget->hasAutoJump());
                            $form->addToggle("Set player is breathing", $playertarget->isBreathing());
                            $form->addToggle("Set player can climb", $playertarget->canClimb());
                            $form->addToggle("Set player can climb walls", $playertarget->canClimbWalls());
                            $form->addInput("Set player's display name", "Enter player's new display name", $playertarget->getDisplayName());
                            $form->addSlider("Set player's fire tick", 0, $max, 1, $playertarget->getFireTicks());
                            $form->addDropdown("Set player's gamemode", ["Adventure", "Survival", "Creative", "Spectator"], $gamemodes2[strtolower($playertarget->getGamemode()->name())]);
                            $form->addToggle("Set player is glidng", $playertarget->isGliding());
                            $form->addSlider("Set player's gravity", 0, $max, 1, intval($playertarget->getGravity()));
                            $form->addToggle("Set player has gravity", $playertarget->hasGravity());
                            $form->addSlider("Set player's health", 0, $playertarget->getMaxHealth(), 1, intval($playertarget->getHealth()));
                            $form->addToggle("Set player is invisible", $playertarget->isInvisible());
                            $form->addSlider("Set player's max air supply ticks", 1, $max, 1, $playertarget->getMaxAirSupplyTicks());
                            $form->addSlider("Set player's max health", 0, $max, 1, $playertarget->getMaxHealth());
                            $form->addSlider("Set player's movement speed", 0, $max, 1, intval($playertarget->getMovementSpeed()));
                            $form->addInput("Set player's name tag (the name ontop of the player)", "Enter player's new name tag", $playertarget->getNameTag());
                            $form->addToggle("Set player's nametag always visible", $playertarget->isNameTagAlwaysVisible());
                            $form->addToggle("Set player's nametag visible", $playertarget->isNameTagVisible());
                            $form->addSlider("Set player's on fire attribute", 0, $max, 1, 0);
                            $form->addSlider("Set player's scale", 0, 5, 1, intval($playertarget->getScale()));
                            $form->addToggle("Set player's on silent", $playertarget->isSilent());
                            $form->addToggle("Set player's on sneaking mode", $playertarget->isSneaking());
                            $form->addToggle("Set player's on sprinting mode", $playertarget->isSprinting());
                            $form->addToggle("Set player's on swimming mode", $playertarget->isSwimming());
                            $player->sendForm($form);
                            return $form;
                    }
                });
                $form->setTitle($playertarget->getName(). "'s Attributes");
                $form->setContent("Select an action to continue");
                $form->addButton(TF::colorize("Get Attributes\n&lGets player's attributes"));
                $form->addButton("Change Attributes\n&lChange player's attributes");
            default:
                $player->sendMessage(TF::colorize("&cERROR: Unknown category: " .$category. ". Available category are: " .implode(", ", $this::PLAYER_MANAGE_CATEGORY)));
            break;
        }
    }
}
