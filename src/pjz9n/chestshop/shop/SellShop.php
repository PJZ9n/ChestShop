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

namespace pjz9n\chestshop\shop;

use pocketmine\level\Position;

class SellShop extends Shop
{
    /** @var int */
    private $limit;

    /**
     * @param int $id
     * @param int $price
     * @param int $itemId
     * @param int $itemMeta
     * @param Position $signPos
     * @param Position $chestPos
     * @param int $limit
     */
    public function __construct(int $id, int $price, int $itemId, int $itemMeta, Position $signPos, Position $chestPos, int $limit)
    {
        $this->limit = $limit;
        parent::__construct($id, $price, $itemId, $itemMeta, $signPos, $chestPos);
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}
