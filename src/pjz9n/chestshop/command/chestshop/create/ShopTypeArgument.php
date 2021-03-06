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

namespace pjz9n\chestshop\command\chestshop\create;

use CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;

class ShopTypeArgument extends StringEnumArgument
{
    protected const VALUES = [
        "buy" => "buy",
    ];

    /**
     * @inheritDoc
     */
    public function parse(string $argument, CommandSender $sender)
    {
        return $this->getValue($argument);
    }

    public function getTypeName(): string
    {
        return "type";
    }
}
