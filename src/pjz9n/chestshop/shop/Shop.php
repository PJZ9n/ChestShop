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

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Position;

class Shop
{
    /** @var int */
    private $id;

    /** @var int */
    private $price;

    /** @var int */
    private $itemId;

    /** @var int */
    private $itemMeta;

    /** @var Position */
    private $signPos;

    /** @var Position */
    private $chestPos;

    /** @var string */
    private $owner;

    /**
     * @param int $id
     * @param int $price
     * @param int $itemId
     * @param int $itemMeta
     * @param Position $signPos
     * @param Position $chestPos
     * @param string $owner
     */
    public function __construct(int $id, int $price, int $itemId, int $itemMeta, Position $signPos, Position $chestPos, string $owner)
    {
        $this->id = $id;
        $this->price = $price;
        $this->itemId = $itemId;
        $this->itemMeta = $itemMeta;
        $this->signPos = $signPos;
        $this->chestPos = $chestPos;
        $this->owner = $owner;
    }

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return ItemFactory::get($this->itemId, $this->itemMeta);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     */
    public function setItemId(int $itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * @return int
     */
    public function getItemMeta(): int
    {
        return $this->itemMeta;
    }

    /**
     * @param int $itemMeta
     */
    public function setItemMeta(int $itemMeta): void
    {
        $this->itemMeta = $itemMeta;
    }

    /**
     * @return Position
     */
    public function getSignPos(): Position
    {
        return $this->signPos;
    }

    /**
     * @return Position
     */
    public function getChestPos(): Position
    {
        return $this->chestPos;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }
}
