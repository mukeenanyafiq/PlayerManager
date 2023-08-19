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
use pocketmine\lang\Language;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
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
    
    public const FIRST_PLACE_TYPE_EXTRA2 = [
        0 => "model", 
        1 => "os", 
        2 => "ip", 
        3 => "port", 
        4 => "ping", 
        5 => "ui", 
        6 => "gui", 
        7 => "controls", 
        8 => "uuid", 
        9 => "health", 
        10 => "position",
        11 => "gamemode"
    ];

    public const PLAYER_MANAGE_CATEGORY = [
        "session",
        "ability"
    ];

    public const AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER = [
        "info",
        "session",
        "ability"
    ];

    public $firstplacetypechoosen = "";

    private const FORMTITLE = "PlayerManager";

    public function parseIniFile(string $filename): array|bool {
        return parse_ini_file($filename);
    }

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
    }

    public function onCommand(CommandSender $commandSender, Command $command, string $commandLabel, array $args): bool {
        switch ($command->getName()) {
            case "playermanager" or "pmanager":
                if (!$commandSender->hasPermission("playermanager.command.playermanager")) {
                    $commandSender->sendMessage("This command is only intended for operators!");
                    return true;
                }

                if (isset($args[0])) {
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
                            $commandSender->sendMessage(TF::colorize("&cUnfortunately, you are in the list of blacklisted players from using PlayerManager form. This means &lyou can't use PlayerManager form anymore."));
                        }
                        return true;
                    }
                }

                if ($commandSender instanceof Player) {
                    if (in_array($commandSender->getName(), $this->getConfig()->get("blacklist"))) {
                        $commandSender->sendMessage(new Translatable("sender.player.blacklisted"));
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
                
                case "session":
                    if (!isset($args[1])) {
                        $player->sendMessage(TF::colorize("&cERROR: No player name was put. Please put the player's name! (TIP: you can use @s to indicate yours)"));
                        return false;
                    }
    
                    if ($args[1] === "@s") {
                        $this->openPlayerManageCategory($player, "session", $player->getName());
                        return true;
                    }

                    if (!$this->getServer()->getPlayerExact($args[1])) {
                        $player->sendMessage(TF::colorize("&cERROR: No player with the name &f" .$args[0]. "&c is online"));
                        return false;
                    }
    
                    $this->openPlayerManageCategory($player, "session", $args[1]);
                break;

                case "ability":
                    if (!isset($args[1])) {
                        $player->sendMessage(TF::colorize("&cERROR: No player name was put. Please put the player's name! (TIP: you can use @s to indicate yours)"));
                        return false;
                    }
    
                    if ($args[1] === "@s") {
                        $this->openPlayerManageCategory($player, "ability", $player->getName());
                        return true;
                    }

                    if (!$this->getServer()->getPlayerExact($args[1])) {
                        $player->sendMessage(TF::colorize("&cERROR: No player with the name &f" .$args[0]. "&c is online"));
                        return false;
                    }
    
                    $this->openPlayerManageCategory($player, "ability", $args[1]);
                break;

                default:
                    $player->sendMessage(TF::colorize("&cERROR: Invalid argument '" .$args[0]. "'. If you want to open player information, you may aswell try /playermanager info <player>"));
                    $player->sendMessage("Available argument: " .implode(", ", $this::AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER));
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

                            if (strtolower($this->firstplacetypechoosen) === "model") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$clientData["DeviceModel"]));
                            } elseif (strtolower($this->firstplacetypechoosen) === "os") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$os[$clientData["DeviceOS"]]));
                            } elseif (strtolower($this->firstplacetypechoosen) === "ip") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getNetworkSession()->getIp()));
                            } elseif (strtolower($this->firstplacetypechoosen) === "port") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getNetworkSession()->getPort()));
                            } elseif (strtolower($this->firstplacetypechoosen) === "ping") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getNetworkSession()->getPing(). 'ms'));
                            } elseif (strtolower($this->firstplacetypechoosen) === "ui") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$UI[$clientData["UIProfile"]]));
                            } elseif (strtolower($this->firstplacetypechoosen) === "gui") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$GUI[$clientData["GuiScale"]]));
                            } elseif (strtolower($this->firstplacetypechoosen) === "controls") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$Controls[$clientData["CurrentInputMode"]]));
                            } elseif (strtolower($this->firstplacetypechoosen) === "uuid") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getUniqueId()));
                            } elseif (strtolower($this->firstplacetypechoosen) === "health") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getHealth(). " HP"));
                            } elseif (strtolower($this->firstplacetypechoosen) === "position") {
                                $form->addButton(TF::colorize($value->getName(). "\n&lX: " .$value->getPosition()->getFloorX(). " Y: " .$value->getPosition()->getFloorY(). " Z: " .$value->getPosition()->getFloorZ()));
                            } elseif (strtolower($this->firstplacetypechoosen) === "gamemode") {
                                $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getGamemode()->name()));
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
            }
        });
        $form->setTitle($playerchoosen->getName());
        $form->setContent(TF::colorize("&aDisplay Name: &f" .$playerchoosen->getDisplayName(). "\n&aModel: &f" .$clientdata["DeviceModel"]. "\n&aOS: &f" .$os[$clientdata["DeviceOS"]]. "\n&aIP: &f" .$playerchoosen->getNetworkSession()->getIp(). "\n&aPort: &f" .$playerchoosen->getNetworkSession()->getPort(). "\n&aPing: &f" .$playerchoosen->getNetworkSession()->getPing(). "ms\n&aUI: &f" .$UI[$clientdata["UIProfile"]]. "\n&aGUI Scale: &f" .$GUI[$clientdata["GuiScale"]]. "\n&aControls: &f" .$Controls[$clientdata["CurrentInputMode"]]. "\n&aUUID: &f" .$playerchoosen->getUniqueId(). "\n&aHealth: &f" .$playerchoosen->getHealth(). " HP\n&aPosition: &fX: " .$playerchoosen->getPosition()->getFloorX() . ", Y: " .$playerchoosen->getPosition()->getFloorY() . ", Z: " .$playerchoosen->getPosition()->getFloorZ(). "\n&aGamemode: &f" .$playerchoosen->getGamemode()->name()));
        $form->addButton(TF::colorize("Session\n&lPlayer's session"));
        $form->addButton(TF::colorize("Ability\n&lPlayer's ability"));
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
                $form->addButton(TF::colorize("Enable Fly\n&lAllows player to fly"));
                $form->addButton(TF::colorize("Enable No-Clip\n&lAllows player to no-clip"));
                $form->addButton(TF::colorize("Disable Fly\n&lDisable ability fly"));
                $form->addButton(TF::colorize("Disable No-Clip\n&lDisable ability no-clip"));
                $player->sendForm($form);
                return $form;
            default:
                $player->sendMessage(TF::colorize("&cERROR: Unknown category: " .$category. ". Available category are: " .implode(", ", $this::PLAYER_MANAGE_CATEGORY)));
            break;
        }
    }
}
