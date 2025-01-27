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
use pocketmine\permission\Permission;
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
        "permissions",
        "ability",
        "attributes",
        "effects"
    ];

    public const AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER = [
        "info",
        "reload",
        "permissions",
        "ability",
        "attributes",
        "effects"
    ];

    public $firstplacetypechoosen = "";

    private const FORMTITLE = "PlayerManager";

    public function onLoad(): void {
        if (in_array($this->getConfig()->get("firstplace"), $this::FIRST_PLACE_TYPE) === false) {
            $this->getLogger()->warning(`Unknown "firstplace" type: '` .strtolower($this->getConfig()->get("firstplace")). `'. Check any typos you might have made. For now, the type will be set to 'model' for default`);
            $this->firstplacetypechoosen = "model";
        } else $this->firstplacetypechoosen = $this->getConfig()->get("firstplace");

        if (!is_array($this->getConfig()->get("blacklist"))) {
            $this->getLogger()->warning(`An error in the 'blacklist' option was found. For now, no player will be blacklisted from using PlayerManager form.`);
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
                    $commandSender->sendMessage("This command is only intended for operators and allowed players!");
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
                            $commandSender->sendMessage("Available argument: " .implode(", ", $this::AVAILABLE_ARGUMENT_CMD_PLAYERMANAGER));
                            return true;
                        }
                    }

                    if ($args[0] === "reload") {
                        $this->getConfig()->reload();
                        $commandSender->sendMessage(TF::colorize("&aPlayerManager's Configuration file (config.yml) has been reloaded."));
                        $this->getLogger()->info("PlayerManager's configuration file (located in " .$this->getDataFolder(). "config.yml) has been reloaded by " .$commandSender->getName(). ".");
                        if (in_array($this->getConfig()->get("firstplace"), $this::FIRST_PLACE_TYPE) === false) {
                            $commandSender->sendMessage(TF::colorize("&eUnknown firstplace type: '" .strtolower($this->getConfig()->get("firstplace")). "'. Check any typos you might have made. For now, the type will be set to 'model' for default"));
                            $this->firstplacetypechoosen = "model";
                        } else $this->firstplacetypechoosen = $this->getConfig()->get("firstplace");

                        if (!is_array($this->getConfig()->get("blacklist"))) {
                            $commandSender->sendMessage(TF::colorize("&eAn error in the 'blacklist' option was found. For now, no player will be blacklisted from using PlayerManager form."));
                        } else {
                            if (count($this->getConfig()->get("blacklist")) > 0) {
                                if (count($this->getConfig()->get("blacklist")) === 1) {
                                    $this->getLogger()->info("There are " .count($this->getConfig()->get("blacklist")). " blacklisted player from using PlayerManager form: " .implode(", ", $this->getConfig()->get("blacklist")));
                                } else {
                                    $this->getLogger()->info("There are " .count($this->getConfig()->get("blacklist")). " blacklisted players from using PlayerManager form: " .implode(", ", $this->getConfig()->get("blacklist")));
                                }
                            }

                            if (in_array($commandSender->getName(), $this->getConfig()->get("blacklist"))) {
                                $commandSender->sendMessage(TF::colorize("&cYou are not allowed to manage players using PlayerManager&l"));
                            }
                        }
                        return true;
                    }
                }

                if ($commandSender instanceof Player) {
                    if (is_array($this->getConfig()->get("blacklist"))) {
                        if (in_array($commandSender->getName(), $this->getConfig()->get("blacklist"))) {
                            $commandSender->sendMessage(TF::colorize("&cSorry, you are not allowed to use this command."));
                            return true;
                        }
                    }

                    $this->openPlayerManagerForm($commandSender, $args);
                } else $commandSender->sendMessage("This command only works for players! Forcing to execute on console might occurs an error");

                return true;
        }
        return true;
    }

    public function openPlayerManagerForm($player, $args) {
        $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows", "Windows", "Dedicated", "Orbis", "PlayStation", "Nintento Switch", "Xbox One"];
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
                            if ($data === null) return true;
                            
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
                                    $form->addButton(TF::colorize($value->getName(). "\n&l" .$value->getGamemode()->name()));
                            }
                        }
                        $player->sendForm($form);
                        return $form;
                    case 1:
                        $form = new SimpleForm(function (Player $player, $data = null) {
                            if ($data === null) return true;
                            
                            switch ($data) {
                                case 0:
                                    $form = new SimpleForm(function (Player $player, $data = null) {
                                        if ($data === null) return true;
                                        
                                        switch ($data) {
                                            case $data:
                                                $target = $this->getServer()->getOfflinePlayer(array_values($this->getServer()->getNameBans()->getEntries())[$data]->getName());
                                                $form = new SimpleForm(function (Player $player, $data = null) use ($target) {
                                                    if ($data === null) return true;
                                                    
                                                    switch ($data) {
                                                        case 0:
                                                            $form = new ModalForm(function (Player $player, $data = null) use ($target) {
                                                                if ($data === null) return true;
            
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
                        } else $this->firstplacetypechoosen = $this->getConfig()->get("firstplace");
                        
                        if (in_array($player->getName(), $this->getConfig()->get("blacklist"))) {
                            $player->sendMessage(TF::colorize("&cYou are not allowed to manage players using PlayerManager&l"));
                        }
                    break;
                }
            });
            $form->setTitle($this::FORMTITLE);
            $form->setContent("PlayerManager v" .$currpl->getDescription()->getVersion(). "\n \nSelect an action to continue");
            $form->addButton("Manage a player\n" .TF::BOLD. "Managing a player");
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
                    $this->openPlayerManageCategory($player, "permissions", $target);
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
        $form->addButton(TF::colorize("Permissions\n&lPlayer's permissions"));
        $form->addButton(TF::colorize("Ability\n&lPlayer's ability"));
        $form->addButton(TF::colorize("Attributes\n&lPlayer's attributes"));
        $form->addButton(TF::colorize("Effects\n&lPlayer's effects"));
        $player->sendForm($form);
        return $form;
    }

    public function openPlayerManageCategory($player, string $category, $target) {
        $playertarget = $this->getServer()->getPlayerExact($target);
        switch ($category) {
            case "permissions":
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

                                $playertarget->setBasePermission($data[0], true);
                                $player->sendMessage(TF::colorize(`&aAdded permission "` .$data[0]. `" to ` .$playertarget->getName()));
                            });
                            $form->setTitle("Add Perm");
                            $form->addInput(TF::colorize("Enter the permission name you would like to give to &l" .$playertarget->getName(). "&r below"), "Enter the permission name");
                            $player->sendForm($form);
                            return $form;
                        case 1:
                            $form = new CustomForm(function (Player $player, $data = null) use ($playertarget) {
                                if ($data === null) {
                                    return true;
                                }

                                $playertarget->unsetBasePermission($data[0]);
                                $player->sendMessage(TF::colorize(`&aRemoved permission "` .$data[0]. `" from ` .$playertarget->getName()));
                            });
                            $form->setTitle("Remove Perm");
                            $form->addInput(TF::colorize("Enter the permission name you would like to remove from &l" .$playertarget->getName(). "&r below"), "Enter the permission name");
                            $player->sendForm($form);
                            return $form;
                    }
                });
                $form->setTitle($playertarget->getName(). "'s Permissions");
                $form->setContent("Select an action to continue");
                $form->addButton(TF::colorize("Add Perm\n&lAdd perm to player"));
                $form->addButton(TF::colorize("Remove Perm\n&lRemove perm from player"));
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
                $form = new SimpleForm(function (Player $player, $data = null) use ($playertarget) {
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
                            $form = new CustomForm(function (Player $player, $data = null) use ($playertarget) {
                                if ($data === null) {
                                    return true;
                                }
        
                                $form = new ModalForm(function (Player $player, $d2 = null) use ($playertarget, $data) {
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

                                            $playertarget->setAirSupplyTicks(intval($data[0]));
                                            $playertarget->setAutoJump(boolval($data[1]));
                                            $playertarget->setBreathing(boolval($data[2]));
                                            $playertarget->setCanClimb(boolval($data[3]));
                                            $playertarget->setCanClimbWalls(boolval($data[4]));
                                            $playertarget->setDisplayName(strval($data[5]));
                                            $playertarget->setFireTicks(intval($data[6]));
                                            $playertarget->setGliding(boolval($data[7]));
                                            $playertarget->setGravity(floatval($data[8]));
                                            $playertarget->setHasGravity(boolval($data[9]));
                                            $playertarget->setHealth(floatval($data[10]));
                                            $playertarget->setMaxAirSupplyTicks(intval($data[11]));
                                            $playertarget->setMaxHealth(intval($data[12]));
                                            $playertarget->setMovementSpeed(floatval($data[13]));
                                            $playertarget->setNameTag(strval($data[14]));
                                            $playertarget->setNameTagAlwaysVisible(boolval($data[15]));
                                            $playertarget->setNameTagVisible(boolval($data[16]));
                                            $playertarget->setOnFire(intval($data[17]));
                                            $playertarget->setScale(floatval($data[18]));
                                            $playertarget->setSilent(boolval($data[19]));
                                            $playertarget->setSneaking(boolval($data[20]));
                                            $playertarget->setSprinting(boolval($data[21]));
                                            $playertarget->setSwimming(boolval($data[22]));
                                            $player->sendMessage(TF::colorize("&a" .$playertarget->getName(). "'s attributes successfully changed! Some attributes may lasts until the player died or disconnected"));
                                        break;
                                    }
                                });
                                $form->setTitle($this::FORMTITLE. " - Confirmation");
                                if ($playertarget->getName() === $player->getName()) {
                                    $form->setContent("Are you sure you want to change your attribute? This change could be a mess and there is no way to revert it back!");
                                } else {
                                    $form->setContent("Are you sure you want to keep change " .$playertarget->getName(). "'s attribute? This change could be a mess and there is no way to revert it back!");
                                }
                                $form->setButton1("Yes");
                                $form->setButton2("No");
                                $player->sendForm($form);
                            });
                            $form->setTitle("Set " .$playertarget->getName(). "'s Attributes");
                            $form->addSlider("Set player's air supply ticks", 0, $playertarget->getMaxAirSupplyTicks(), 1, $playertarget->getAirSupplyTicks());
                            $form->addToggle("Set player's autojump", $playertarget->hasAutoJump());
                            $form->addToggle("Set player is breathing", $playertarget->isBreathing());
                            $form->addToggle("Set player can climb", $playertarget->canClimb());
                            $form->addToggle("Set player can climb walls", $playertarget->canClimbWalls());
                            $form->addInput("Set player's display name", "Enter player's new display name", $playertarget->getDisplayName());
                            $form->addInput("Set player on fire for an inputted seconds", "Number", strval($playertarget->getFireTicks()));
                            $form->addToggle("Set player is glidng", $playertarget->isGliding());
                            $form->addInput("Set player's gravity", "Number", strval($playertarget->getGravity()));
                            $form->addToggle("Set player has gravity", $playertarget->hasGravity());
                            $form->addInput("Set player's health", "Number", strval($playertarget->getHealth()));
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
                $form = new SimpleForm(function (Player $player, $data = null) use ($playertarget) {
                    if ($data === null) {
                        return true;
                    }

                    switch ($data) {
                        case 0:
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
                                                    $form->addLabel("Change remaining duration of the effect\n \n(Putting numbers lower than 1, other characters rather than numbers or empty number will result in removing the effect from the player.)");
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
                $form->addButton(TF::colorize("Manage Effects\n&lManage all effects"));
                $player->sendForm($form);
                return $form;
            default:
                $player->sendMessage(TF::colorize("&cERROR: Unknown category: " .$category. ". Available category are: " .implode(", ", $this::PLAYER_MANAGE_CATEGORY)));
            break;
        }
    }
}
