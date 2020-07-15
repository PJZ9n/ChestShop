<?php

/**
 * Copyright (c) 2020 PJZ9n.
 *
 * This file is part of ChestShop.
 *
 * ChestShop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ChestShop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ChestShop. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace pjz9n\chestshop\command\chestshop;

use CortexPE\Commando\BaseCommand;
use pjz9n\chestshop\command\chestshop\create\CreateCommand;
use pjz9n\chestshop\ShopManager;
use pocketmine\command\CommandSender;
use pocketmine\lang\BaseLang;
use pocketmine\plugin\Plugin;

class ChestShopCommand extends BaseCommand
{
    /** @var BaseLang */
    private $lang;

    /** @var ShopManager */
    private $shopManager;

    public function __construct(Plugin $plugin, BaseLang $lang, ShopManager $shopManager)
    {
        $this->lang = $lang;
        $this->shopManager = $shopManager;
        parent::__construct(
            $plugin,
            "chestshop",
            $this->lang->translateString("command.chestshop.description"),
            ["cs"]
        );
    }

    protected function prepare(): void
    {
        $this->setPermission("chestshop.command.chestshop");
        $this->registerSubCommand(new CreateCommand(
            $this->lang,
            $this->shopManager
        ));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $this->sendUsage();
    }
}
