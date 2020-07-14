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

namespace pjz9n\chestshop\log;

use DateTime;

class Log
{
    /** @var int */
    private $shopId;

    /** @var string */
    private $player;

    /** @var DateTime */
    private $date;

    /**
     * @param int $shopId
     * @param string $player
     * @param DateTime $date
     */
    public function __construct(int $shopId, string $player, DateTime $date)
    {
        $this->shopId = $shopId;
        $this->player = $player;
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }

    /**
     * @return string
     */
    public function getPlayer(): string
    {
        return $this->player;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }
}
