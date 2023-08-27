<?php

declare(strict_types=1);

namespace mukeenanyafiq\PlayerManager;

/* These forms class are originally from jojoe77777\FormAPI */
use mukeenanyafiq\FormAPI\SimpleForm;
use mukeenanyafiq\FormAPI\CustomForm;
use mukeenanyafiq\FormAPI\ModalForm;

/* Pocketmine classes */
use pocketmine\color\Color;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Listener;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
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

    public const PLAYER_MANAGE_CATEGORY = [
        "session",
        "ability",
        "attributes",
        "effects"
    ];

    public const AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER = [
        "info",
        "reload",
        "session",
        "ability",
        "attributes",
        "effects"
    ];

    public $firstplacetypechoosen = "";

    private const FORMTITLE = "PlayerManager";

    public function onLoad(): void {
        $this->getLogger()->info("Plugin loaded");
        if (in_array($this->getConfig()->get("firstplace"), $this::FIRST_PLACE_TYPE) === false) {
            $this->getLogger()->warning("Unknown firstplace type: '" .strtolower($this->getConfig()->get("firstplace")). "'. Check any misspells or typos you might have made. For now, the type will be set to 'model' for default");
            $this->firstplacetypechoosen = "model";
        } else {
            $this->firstplacetypechoosen = $this->getConfig()->get("firstplace");
        }
        if (!is_array($this->getConfig()->get("blacklist"))) {
            $this->getLogger()->warning(`"blacklist" option is not an array class. If you don't know, an array class looks like this: "[]". Change the "blacklist" option class to array with the example putted on this message. For now, no player will be blacklisted from using PlayerManager form.`);
        } else {
            if (count($this->getConfig()->get("blacklist")) > 0) {
                if (count($this->getConfig()->get("blacklist")) === 1) {
                    $this->getLogger()->info("There are " .count($this->getConfig()->get("blacklist")). " blacklisted player from using PlayerManager form: " .implode(", ", $this->getConfig()->get("blacklist")));
                } else {
                    $this->getLogger()->info("There are " .count($this->getConfig()->get("blacklist")). " blacklisted players from using PlayerManager form: " .implode(", ", $this->getConfig()->get("blacklist")));
                }
            }
        }
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
                        $commandSender->sendMessage(TF::colorize("&aPlayerManager's Configuration file (config.yml) has been reloaded."));
                        $this->getLogger()->info("PlayerManager's configuration file (located in " .$this->getDataFolder(). "config.yml) has been reloaded by " .$commandSender->getName(). ".");
                        if (in_array($this->getConfig()->get("firstplace"), $this::FIRST_PLACE_TYPE) === false) {
                            $commandSender->sendMessage(TF::colorize("&eUnknown firstplace type: '" .strtolower($this->getConfig()->get("firstplace")). "'. Check any misspells or typos you might have made. For now, the type will be set to 'model' for default"));
                            $this->firstplacetypechoosen = "model";
                        } else {
                            $this->firstplacetypechoosen = $this->getConfig()->get("firstplace");
                        } 
                        if (!is_array($this->getConfig()->get("blacklist"))) {
                            $commandSender->sendMessage(TF::colorize("&e'blacklist' option is not an array class. If you don't know, an array class looks like this: '[]'. Change the 'blacklist' option class to array. For now, no player will be blacklisted from using PlayerManager form."));
                        } else {
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
                        }
                        return true;
                    }
                }

                if ($commandSender instanceof Player) {
                    if (is_array($this->getConfig()->get("blacklist"))) {
                        if (in_array($commandSender->getName(), $this->getConfig()->get("blacklist"))) {
                            $commandSender->sendMessage(TF::colorize("&cSorry, you are not allowed to use this command. Even though you have the permission to use the command, you're on the list of the blacklisted players. If you have the access to the server's files, you can delete your username off from the list in the configuration file."));
                            return true;
                        }
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
                            $language = Server::getInstance()->getLanguage();
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
                
                case 2:
                    $this->openPlayerManageCategory($player, "attributes", $target);
                break;
                
                case 3:
                    $this->openPlayerManageCategory($player, "effects", $target);
                break;
            }
        });
        $form->setTitle($playerchoosen->getName());
        $form->setContent(TF::colorize("&aDisplay Name: &f" .$playerchoosen->getDisplayName(). "\n&aModel: &f" .$clientdata["DeviceModel"]. "\n&aOS: &f" .$os[$clientdata["DeviceOS"]]. "\n&aIP: &f" .$playerchoosen->getNetworkSession()->getIp(). "\n&aPort: &f" .$playerchoosen->getNetworkSession()->getPort(). "\n&aPing: &f" .$playerchoosen->getNetworkSession()->getPing(). "ms\n&aUI: &f" .$UI[$clientdata["UIProfile"]]. "\n&aGUI Scale: &f" .$GUI[$clientdata["GuiScale"]]. "\n&aControls: &f" .$Controls[$clientdata["CurrentInputMode"]]. "\n&aUUID: &f" .$playerchoosen->getUniqueId(). "\n&aHealth: &f" .$playerchoosen->getHealth(). " HP\n&aPosition: &fX: " .$playerchoosen->getPosition()->getFloorX() . ", Y: " .$playerchoosen->getPosition()->getFloorY() . ", Z: " .$playerchoosen->getPosition()->getFloorZ(). "\n&aGamemode: &f" .$playerchoosen->getGamemode()->name()));
        $form->addButton(TF::colorize("Session\n&lPlayer's session"));
        $form->addButton(TF::colorize("Ability\n&lPlayer's ability"));
        $form->addButton(TF::colorize("Attributes\n&lPlayer's attributes"));
        $form->addButton(TF::colorize("Effects\n&lPlayer's effects"));
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
                            $player->sendMessage("==== " .$playertarget->getName(). "'s attributes - " .$this::FORMTITLE. " ====");
                            $player->sendMessage(TF::colorize("&aAbsorption: &f" .$playertarget->getAbsorption(). "\n&aAir Supply Ticks: &f" .$playertarget->getAirSupplyTicks(). "\n&aHas Auto Jump: &f" .var_export($playertarget->hasAutoJump(), true). "\n&aIs Breathing: &f" .var_export($playertarget->isBreathing(), true). "\n&aCan Climb: &f" .var_export($playertarget->canClimb(), true). "\n&aCan Climb Walls: &f" .var_export($playertarget->canClimbWalls(), true). "\n&aDisplay Name: &f" .$playertarget->getDisplayName(). "\n&aFire Ticks: &f" .$playertarget->getFireTicks(). "\n&aGamemode: &f" .$playertarget->getGamemode()->name(). "\n&aIs Gliding: &f" .var_export($playertarget->isGliding(), true). "\n&aGravity: &f" .$playertarget->getGravity(). "\n&aHas Gravity: &f" .var_export($playertarget->hasGravity(), true). "\n&aHealth: &f" .$playertarget->getHealth(). " HP\n&aIs Invisible: &f" .var_export($playertarget->isInvisible(), true). "\n&aMaximum Air Supply Ticks: &f" .$playertarget->getMaxAirSupplyTicks(). "\n&aMaximum Health: &f" .$playertarget->getMaxHealth(). "\n&aMovement Speed: &f" .$playertarget->getMovementSpeed(). "\n&aName Tag: &f" .$playertarget->getNameTag(). "\n&aIs Name Tag Always Visible: &f" .var_export($playertarget->isNameTagAlwaysVisible(), true). "\n&aIs Name Tag Visible: &f" .var_export($playertarget->isNameTagVisible(), true). "\n&aIs On Fire: &f" .var_export($playertarget->isOnFire(), true). "\n&aScale: &f" .$playertarget->getScale(). "\n&aIs Silent: &f" .var_export($playertarget->isSilent(), true). "\n&aIs Sneaking: &f" .var_export($playertarget->isSneaking(), true). "\n&aIs Sprinting: &f" .var_export($playertarget->isSprinting(), true). "\n&aIs Swimming: &f" .var_export($playertarget->isSwimming(), true)));
                            $player->sendMessage("===========================");
                        break;

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
                                            // If the player's inputted max health value is less than 1, sets the player's inputted max health value to the player's original max health value
                                            if (intval($data[15]) < 1) {
                                                $data[15] = $playertarget->getMaxHealth();
                                                $playertarget->sendMessage(TF::colorize("&eThe inputted max health value is less than 1. Resetting the value to the player's original max health value (" .$playertarget->getMaxHealth(). ")"));
                                            }

                                            // If the player's inputted scale value is less than 1, sets the player's inputted scale value to the normal scale value
                                            if (floatval($data[21]) < 1) {
                                                $data[21] = 1;
                                                $playertarget->sendMessage(TF::colorize("&eThe inputted scale value is less than 1. Resetting the value to the normal scale value (1)"));
                                            }

                                            $playertarget->setAbsorption(floatval($data[0]));
                                            $playertarget->setAirSupplyTicks(intval($data[1]));
                                            $playertarget->setAutoJump(boolval($data[2]));
                                            $playertarget->setBreathing(boolval($data[3]));
                                            $playertarget->setCanClimb(boolval($data[4]));
                                            $playertarget->setCanClimbWalls(boolval($data[5]));
                                            $playertarget->setDisplayName(strval($data[6]));
                                            $playertarget->setFireTicks(intval($data[7]));
                                            $playertarget->setGamemode(GameMode::fromString($gamemodes[$data[8]]));
                                            $playertarget->setGliding(boolval($data[9]));
                                            $playertarget->setGravity(floatval($data[10]));
                                            $playertarget->setHasGravity(boolval($data[11]));
                                            $playertarget->setHealth(floatval($data[12]));
                                            $playertarget->setInvisible(boolval($data[13]));
                                            $playertarget->setMaxAirSupplyTicks(intval($data[14]));
                                            $playertarget->setMaxHealth(intval($data[15]));
                                            $playertarget->setMovementSpeed(floatval($data[16]));
                                            $playertarget->setNameTag(strval($data[17]));
                                            $playertarget->setNameTagAlwaysVisible(boolval($data[18]));
                                            $playertarget->setNameTagVisible(boolval($data[19]));
                                            $playertarget->setOnFire(intval($data[20]));
                                            $playertarget->setScale(floatval($data[21]));
                                            $playertarget->setSilent(boolval($data[22]));
                                            $playertarget->setSneaking(boolval($data[23]));
                                            $playertarget->setSprinting(boolval($data[24]));
                                            $playertarget->setSwimming(boolval($data[25]));
                                            $player->sendMessage(TF::colorize("&a" .$playertarget->getName(). "'s attributes successfully changed! Some attributes may lasts until the player died or disconnected"));
                                        break;
                                    }
                                });
                                $form->setTitle($this::FORMTITLE. " - Confirmation");
                                if ($playertarget->getName() === $player->getName()) {
                                    $form->setContent("Are you sure you want to keep changing your attribute? This change could be a mess and there is no way to revert it back unless you remembered/saved the attributes OR you died/disconnected!");
                                } else {
                                    $form->setContent("Are you sure you want to keep changing " .$playertarget->getName(). "'s attribute? This change could be a mess and there is no way to revert it back unless you remembered/saved the attributes OR the player died/disconnected!");
                                }
                                $form->setButton1("Yes");
                                $form->setButton2("No");
                                $player->sendForm($form);
                            });
                            $form->setTitle("Set " .$playertarget->getName(). "'s Attributes");
                            $form->addSlider("Set player's absorption", 0, $max, 1, intval($playertarget->getAbsorption()));
                            $form->addSlider("Set player's air supply ticks", 0, $playertarget->getMaxAirSupplyTicks(), 1, $playertarget->getAirSupplyTicks());
                            $form->addToggle("Set player's autojump", $playertarget->hasAutoJump());
                            $form->addToggle("Set player is breathing", $playertarget->isBreathing());
                            $form->addToggle("Set player can climb", $playertarget->canClimb());
                            $form->addToggle("Set player can climb walls", $playertarget->canClimbWalls());
                            $form->addInput("Set player's display name", "Enter player's new display name", $playertarget->getDisplayName());
                            $form->addInput("Set player on fire for an inputted seconds", "Number", strval($playertarget->getFireTicks()));
                            $form->addDropdown("Set player's gamemode", ["Adventure Mode", "Survival Mode", "Creative Mode", "Spectator Mode"], $gamemodes2[strtolower($playertarget->getGamemode()->name())]);
                            $form->addToggle("Set player is glidng", $playertarget->isGliding());
                            $form->addInput("Set player's gravity", "Number", strval($playertarget->getGravity()));
                            $form->addToggle("Set player has gravity", $playertarget->hasGravity());
                            $form->addInput("Set player's health", "Number", strval($playertarget->getHealth()));
                            $form->addToggle("Set player is invisible", $playertarget->isInvisible());
                            $form->addInput("Set player's max air supply ticks", "Number", strval($playertarget->getMaxAirSupplyTicks()));
                            $form->addInput("Set player's max health", "Number", strval($playertarget->getMaxHealth()));
                            $form->addInput("Set player's movement speed", "Number", strval($playertarget->getMovementSpeed()));
                            $form->addInput("Set player's name tag (the name ontop of the player)", "Enter player's new name tag", $playertarget->getNameTag());
                            $form->addToggle("Set player's nametag always visible", $playertarget->isNameTagAlwaysVisible());
                            $form->addToggle("Set player's nametag visible", $playertarget->isNameTagVisible());
                            $form->addToggle("Set player's on fire", $playertarget->isOnFire());
                            $form->addInput("Set player's scale", "Number", strval($playertarget->getScale()));
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
                $form->addButton(TF::colorize("Change Attributes\n&lChange player's attributes"));
                $player->sendForm($form);
                return $form;
            case "effects":
                $effectlist = [
                    0 => VanillaEffects::ABSORPTION(),
                    1 => VanillaEffects::BLINDNESS(),
                    2 => VanillaEffects::CONDUIT_POWER(),
                    3 => VanillaEffects::DARKNESS(),
                    4 => VanillaEffects::FATAL_POISON(),
                    5 => VanillaEffects::FIRE_RESISTANCE(),
                    6 => VanillaEffects::HASTE(),
                    7 => VanillaEffects::HEALTH_BOOST(),
                    8 => VanillaEffects::HUNGER(),
                    9 => VanillaEffects::INSTANT_DAMAGE(),
                    10 => VanillaEffects::INSTANT_HEALTH(),
                    11 => VanillaEffects::INVISIBILITY(),
                    12 => VanillaEffects::JUMP_BOOST(),
                    13 => VanillaEffects::LEVITATION(),
                    14 => VanillaEffects::MINING_FATIGUE(),
                    15 => VanillaEffects::NAUSEA(),
                    16 => VanillaEffects::NIGHT_VISION(),
                    17 => VanillaEffects::POISON(),
                    18 => VanillaEffects::REGENERATION(),
                    19 => VanillaEffects::RESISTANCE(),
                    20 => VanillaEffects::SATURATION(),
                    21 => VanillaEffects::SLOWNESS(),
                    22 => VanillaEffects::SPEED(),
                    23 => VanillaEffects::STRENGTH(),
                    24 => VanillaEffects::WATER_BREATHING(),
                    25 => VanillaEffects::WEAKNESS(),
                    26 => VanillaEffects::WITHER()
                ];

                $form = new SimpleForm(function (Player $player, $data = null) use ($playertarget, $effectlist) {
                    if ($data === null) {
                        return true;
                    }

                    switch ($data) {
                        case 0:
                            $form = new CustomForm(function (Player $player, $data = null) use ($playertarget, $effectlist) {
                                $language = Server::getInstance()->getLanguage();

                                if ($data === null) {
                                    return true;
                                }
                                
                                // The input data will be turned into integer. Integer only accepts numbers. No decimals and no other character than numbers.
                                // If you tried to put other characters than numbers in it or putting no number in the input, intval() will return number "0" as failure

                                // 0 seconds is an instant expired effect. An effect with 0 seconds is like giving the player nothing
                                // If the seconds inputted is under 1 second, then it will return as 30 seconds cause that's the default effect duration if you use /effect command if no duration specified
                                if (intval($data[1]) < 1) {
                                    $data[1] = 30;
                                }

                                // Minecraft produces ticks, and a second on real life time equals to 20 in-game ticks.
                                // Multiplying the real effect seconds by 20 ticks, and the game will acts as the effect seconds was using the real effect seconds.
                                // If you tried to print $calculated, it will return the real effect seconds but multiplied by 20.
                                $calculated = intval($data[1]) * 20;

                                $playertarget->getEffects()->add(new EffectInstance($effectlist[$data[0]], intval($calculated), intval($data[2]), $data[3]));
                                $player->sendMessage(TF::colorize("&aSuccessfully added a new effect " .$language->translateString($effectlist[$data[0]]->getName()->getText()). " to " .$playertarget->getName(). " for " .intval($data[1]). " seconds with amplifier " .$data[2]. "!"));
                            });
                            $form->setTitle("Add Effects");
                            $form->addDropdown("Select an effect to be added to " .$playertarget->getName(), ["Absorption", "Blindness", "Conduit Power", "Darkness", "Fatal Poison", "Fire Resistance", "Haste", "Health Boost", "Hunger", "Instant Damage", "Instant Health", "Invisibility", "Jump Boost", "Levitation", "Mining Fatigue", "Nausea", "Night Vision", "Poison", "Regeneration", "Resistance", "Saturation", "Slowness", "Speed", "Strength", "Water Breathing", "Weakness", "Wither"]);
                            $form->addInput("Put how many duration you want to effect to apply until expired as seconds (optional)", "Number");
                            $form->addSlider("Put how strong the effect that is applied (optional)", 0, 255, 1);
                            $form->addToggle("Particle effect is visible to everyone (leave it as true for default)", true);
                            $player->sendForm($form);
                            return $form;
                        case 1:
                            $form = new SimpleForm(function (Player $player, $data = null) use ($playertarget) {
                                if ($data === null) {
                                    return true;
                                }

                                switch ($data) {
                                    case $data:
                                        $effectchoosen = $playertarget->getEffects()->get(array_values($playertarget->getEffects()->all())[$data]->getType());
                                        $form = new SimpleForm(function (Player $player, $data = null) use ($effectchoosen, $playertarget) {
                                            if ($data === null) {
                                                return true;
                                            }

                                            switch ($data) {
                                                case 0:
                                                    $form = new CustomForm(function (Player $player, $data = null) use ($effectchoosen, $playertarget) {
                                                        $language = Server::getInstance()->getLanguage();

                                                        if ($data === null) {
                                                            return true;
                                                        }

                                                        $type = $effectchoosen->getType();
                                                        $duration = $effectchoosen->getDuration();
                                                        $amplifier = $effectchoosen->getAmplifier();
                                                        $visible = $effectchoosen->isVisible();
                                                        $ambient = $data[1];
                                                        $color = $effectchoosen->getColor();

                                                        $playertarget->getEffects()->remove($type);
                                                        $playertarget->getEffects()->add(new EffectInstance($type, $duration, $amplifier, $visible, $ambient, $color));
                                                        $player->sendMessage(TF::colorize("&aEffect is ambient for effect " .$language->translateString($effectchoosen->getType()->getName()->getText()). " successfully changed to " .var_export($data[1], true). " in the player " .$playertarget->getName(). "!"));
                                                    });
                                                    $form->setTitle("Set Ambient");
                                                    $form->addLabel("Enabling ambient to an effect will make the effect indicates are from game environment, not from plugin");
                                                    $form->addToggle("Is the effect from ambient environment", $effectchoosen->isAmbient());
                                                    $player->sendForm($form);
                                                    return $form;
                                                case 1:
                                                    $form = new CustomForm(function (Player $player, $data = null) use ($effectchoosen, $playertarget) {
                                                        $language = Server::getInstance()->getLanguage();

                                                        if ($data === null) {
                                                            return true;
                                                        }

                                                        $type = $effectchoosen->getType();
                                                        $duration = $effectchoosen->getDuration();
                                                        $amplifier = intval($data[1]);
                                                        $visible = $effectchoosen->isVisible();
                                                        $ambient = $effectchoosen->isAmbient();
                                                        $color = $effectchoosen->getColor();

                                                        $playertarget->getEffects()->remove($type);
                                                        $playertarget->getEffects()->add(new EffectInstance($type, $duration, $amplifier, $visible, $ambient, $color));
                                                        $effectchoosen->setAmplifier(intval($data[1]));
                                                        $player->sendMessage(TF::colorize("&aAmplifier for effect " .$language->translateString($effectchoosen->getType()->getName()->getText()). " successfully changed to " .$data[1]. " in the player " .$playertarget->getName(). "!"));
                                                    });
                                                    $form->setTitle("Set Amplifier");
                                                    $form->addLabel("Changes the strength/amplifier of the effect");
                                                    $form->addSlider("Effect strength/amplifier", 0, 255, 1, $effectchoosen->getAmplifier());
                                                    $player->sendForm($form);
                                                    return $form;
                                                case 2:
                                                    $form = new CustomForm(function (Player $player, $data = null) use ($effectchoosen, $playertarget) {
                                                        $language = Server::getInstance()->getLanguage();

                                                        if ($data === null) {
                                                            return true;
                                                        }

                                                        $type = $effectchoosen->getType();
                                                        $duration = $effectchoosen->getDuration();
                                                        $amplifier = $effectchoosen->getAmplifier();
                                                        $visible = $effectchoosen->isVisible();
                                                        $ambient = $effectchoosen->isAmbient();
                                                        $color = new Color(intval($data[1]), intval($data[2]), intval($data[3]), intval($data[4]));

                                                        $playertarget->getEffects()->remove($type);
                                                        $playertarget->getEffects()->add(new EffectInstance($type, $duration, $amplifier, $visible, $ambient, $color));
                                                        $player->sendMessage(TF::colorize("&aColor for particle effect " .$language->translateString($effectchoosen->getType()->getName()->getText()). " successfully changed to R: " .$data[1]. " G: " .$data[2]. " B: " .$data[3]. " A: " .$data[4]. " in the player " .$playertarget->getName(). "!"));
                                                    });
                                                    $form->setTitle("Set Color");
                                                    $form->addLabel("Changes the color of the effect");
                                                    $form->addSlider("R (Red)", 0, 255, 1, $effectchoosen->getColor()->getR());
                                                    $form->addSlider("G (Green)", 0, 255, 1, $effectchoosen->getColor()->getG());
                                                    $form->addSlider("B (Blue)", 0, 255, 1, $effectchoosen->getColor()->getB());
                                                    $form->addSlider("A (Alpha/Transparency)", 0, 255, 1, $effectchoosen->getColor()->getA());
                                                    $player->sendForm($form);
                                                    return $form;
                                                case 3:
                                                    $form = new CustomForm(function (Player $player, $data = null) use ($effectchoosen, $playertarget) {
                                                        $language = Server::getInstance()->getLanguage();

                                                        if ($data === null) {
                                                            return true;
                                                        }

                                                        if ($data[1] === null) {
                                                            $data[1] = 0;
                                                        }

                                                        $tickToDuration = intval($data[1]) * 20;

                                                        $type = $effectchoosen->getType();
                                                        $duration = intval($tickToDuration);
                                                        $amplifier = $effectchoosen->getAmplifier();
                                                        $visible = $effectchoosen->isVisible();
                                                        $ambient = $effectchoosen->isAmbient();
                                                        $color = $effectchoosen->getColor();

                                                        $playertarget->getEffects()->remove($type);
                                                        $playertarget->getEffects()->add(new EffectInstance($type, $duration, $amplifier, $visible, $ambient, $color));
                                                        if ($tickToDuration < 1) {
                                                            $player->sendMessage("Removed effect " .$language->translateString($effectchoosen->getType()->getName()->getText()). " from " .$playertarget->getName(). ".");
                                                        } else {
                                                            $player->sendMessage(TF::colorize("&aDuration for effect " .$language->translateString($effectchoosen->getType()->getName()->getText()). " successfully changed to " .$data[1]. " seconds in the player " .$playertarget->getName(). "!"));
                                                        }
                                                    });
                                                    $form->setTitle("Set Duration");
                                                    $form->addLabel("Change remaining duration of the effect\n \n(Putting numbers lower than 1, other characters rather than numbers or empty number will result in removing the effect from the player. You should leave the duration empty and press 'Submit' if you want to remove the effect from the player)");
                                                    $form->addInput("Put how long the duration by seconds", "Number");
                                                    $player->sendForm($form);
                                                    return $form;
                                                case 4:
                                                    $form = new CustomForm(function (Player $player, $data = null) use ($effectchoosen, $playertarget) {
                                                        $language = Server::getInstance()->getLanguage();

                                                        if ($data === null) {
                                                            return true;
                                                        }

                                                        $type = $effectchoosen->getType();
                                                        $duration = $effectchoosen->getDuration();
                                                        $amplifier = $effectchoosen->getAmplifier();
                                                        $visible = $data[1];
                                                        $ambient = $effectchoosen->isAmbient();
                                                        $color = $effectchoosen->getColor();

                                                        $playertarget->getEffects()->remove($type);
                                                        $playertarget->getEffects()->add(new EffectInstance($type, $duration, $amplifier, $visible, $ambient, $color));
                                                        $player->sendMessage(TF::colorize("&aParticle visibility for effect " .$language->translateString($effectchoosen->getType()->getName()->getText()). " successfully changed to " .var_export($data[1], true). " in the player " .$playertarget->getName(). "!"));
                                                    });
                                                    $form->setTitle("Set Visible");
                                                    $form->addLabel("Sets whether the effect particle is visible to everyone or nobody including you");
                                                    $form->addToggle("Is the effect particle visible", $effectchoosen->isVisible());
                                                    $player->sendForm($form);
                                                    return $form;
                                                case 5:
                                                    $form = new ModalForm(function (Player $player, $data = null) use ($effectchoosen, $playertarget) {
                                                        if ($data === null) {
                                                            return true;
                                                        }

                                                        switch ($data) {
                                                            case 0:
                                                                $player->sendMessage("The action has been canceled.");
                                                            break;

                                                            case 1:
                                                                $type = $effectchoosen->getType();
                                                                $duration = $effectchoosen->getDuration();
                                                                $amplifier = $effectchoosen->getAmplifier();
                                                                $visible = $effectchoosen->isVisible();
                                                                $ambient = $effectchoosen->isAmbient();
        
                                                                $playertarget->getEffects()->remove($type);
                                                                $playertarget->getEffects()->add(new EffectInstance($type, $duration, $amplifier, $visible, $ambient));
                                                                $player->sendMessage(TF::colorize("&aSuccessfully resetted the color of the particle effect!"));
                                                            break;
                                                        }
                                                    });
                                                    $form->setTitle("Reset Color");
                                                    $form->setContent("Are you sure you want to reset the effect particle color? The current color of the particle effect (R: " .$effectchoosen->getColor()->getR(). " G: " .$effectchoosen->getColor()->getG(). " B: " .$effectchoosen->getColor()->getB(). " A: " .$effectchoosen->getColor()->getA(). ") will change to default as soon as you resetted the color. Continue?");
                                                    $form->setButton1("Yes");
                                                    $form->setButton2("No");
                                                    $player->sendForm($form);
                                                    return $form;
                                            }
                                        });
                                        $form->setTitle($effectchoosen->getType()->getName()->getText());
                                        $form->setContent(TF::colorize("Information about this applied effect in player " .$playertarget->getName(). ":\n \n&aAmplifier: &f" .$effectchoosen->getAmplifier(). "\n&aColor: &fR: " .$effectchoosen->getColor()->getR(). " G: " .$effectchoosen->getColor()->getG(). " B: " .$effectchoosen->getColor()->getB(). " A: " .$effectchoosen->getColor()->getA(). "\n&aDuration remaining: &f" .floor($effectchoosen->getDuration() / 20). "\n&aEffect Level: &f" .$effectchoosen->getEffectLevel(). "\n&aHas expired: &f" .var_export($effectchoosen->hasExpired(), true). "\n&aIs ambient: &f" .var_export($effectchoosen->isAmbient(), true). "\n&aParticle visible to everyone: &f" .var_export($effectchoosen->isVisible(), true). "\n \nWhat do you want to do with this effect?"));
                                        $form->addButton(TF::colorize("Set Ambient\n&lSet effect from ambient"));
                                        $form->addButton(TF::colorize("Set Amplifier\n&lSet effect's strength"));
                                        $form->addButton(TF::colorize("Set Color\n&lSet effect's color"));
                                        $form->addButton(TF::colorize("Set Duration\n&lSet effect's duration"));
                                        $form->addButton(TF::colorize("Set Visible\n&lSet effect is visible"));
                                        $form->addButton(TF::colorize("Reset color\n&lReset color to default"));
                                        $player->sendForm($form);
                                        return $form;
                                }
                            });
                            $form->setTitle("Manage Effects");
                            $form->setContent("Select an effect to be managed");
                            foreach ($playertarget->getEffects()->all() as $value) {
                                $language = Server::getInstance()->getLanguage();
                                $form->addButton($language->translateString($value->getType()->getName()->getText()). "\n" .floor($value->getDuration() / 20). " secs left");
                            }
                            $player->sendForm($form);
                            return $form;
                    }
                });
                $form->setTitle($playertarget->getName(). "'s Effects");
                $form->setContent("Select an action to continue");
                $form->addButton(TF::colorize("Add Effect (/effect)\n&lAdd effect to player"));
                $form->addButton(TF::colorize("Manage Effects\n&lManage all effects"));
                $player->sendForm($form);
                return $form;
            default:
                $player->sendMessage(TF::colorize("&cERROR: Unknown category: " .$category. ". Available category are: " .implode(", ", $this::PLAYER_MANAGE_CATEGORY)));
            break;
        }
    }
}
