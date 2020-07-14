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

namespace pjz9n\chestshop;

use pjz9n\chestshop\database\ShopDatabase;
use pjz9n\chestshop\log\BuyLog;
use pjz9n\chestshop\log\SellLog;
use pjz9n\chestshop\shop\BuyShop;
use pjz9n\chestshop\shop\SellShop;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;

class ShopManager
{
    /** @var ShopDatabase */
    private $shopDatabase;

    /** @var BuyShop[] */
    private $buyShops;

    /** @var BuyShop[] */
    private $removedBuyShops = [];

    /** @var BuyLog[] */
    private $buyLogs;

    /** @var SellShop[] */
    private $sellShops;

    /** @var SellShop[] */
    private $removedSellShops = [];

    /** @var SellLog[] */
    private $sellLogs;

    /**
     * @param ShopDatabase $shopDatabase
     */
    public function __construct(ShopDatabase $shopDatabase)
    {
        $this->shopDatabase = $shopDatabase;
        $this->buyShops = $this->shopDatabase->getAllBuyShop();
        $this->buyLogs = $this->shopDatabase->getAllBuyLog();
        $this->sellShops = $this->shopDatabase->getAllSellShop();
        $this->sellLogs = $this->shopDatabase->getAllSellLog();
    }

    public function __destruct()
    {
        foreach ($this->buyShops as $buyShop) {
            $this->shopDatabase->saveBuyShop($buyShop);
        }
        foreach ($this->removedBuyShops as $removedBuyShop) {
            $this->shopDatabase->removeBuyShop($removedBuyShop);
        }
        foreach ($this->buyLogs as $buyLog) {
            $this->shopDatabase->saveBuyLog($buyLog);
        }
        foreach ($this->sellShops as $sellShop) {
            $this->shopDatabase->saveSellShop($sellShop);
        }
        foreach ($this->removedSellShops as $removedSellShop) {
            $this->shopDatabase->removeSellShop($removedSellShop);
        }
        foreach ($this->sellLogs as $sellLog) {
            $this->shopDatabase->saveSellLog($sellLog);
        }
        $this->shopDatabase->close();
    }

    /**
     * @param int $price
     * @param Item $item
     * @param Position $signPos
     * @param Position $chestPos
     * @param Player $owner
     */
    public function addBuyShop(int $price, Item $item, Position $signPos, Position $chestPos, Player $owner): void
    {
        $id = $this->buyShops[array_key_last($this->buyShops)]->getId() + 1;//TODO
        $this->buyShops[$id] = new BuyShop(
            $id,
            $price,
            $item->getId(),
            $item->getDamage(),
            $signPos,
            $chestPos,
            $owner->getName()
        );
    }

    /**
     * @param int $id
     */
    public function removeBuyShop(int $id): void
    {
        foreach ($this->buyShops as $buyShop) {
            if ($buyShop->getId() === $id) {
                $this->removedBuyShops[] = $this->buyShops[$id];
                unset($this->buyShops[$id]);
            }
        }
    }

    /**
     * @param Position $signPos
     *
     * @return BuyShop|null
     */
    public function getBuyShopBySignPos(Position $signPos): ?BuyShop
    {
        foreach ($this->buyShops as $buyShop) {
            if ($buyShop->getSignPos()->equals($signPos)) {
                return $buyShop;
            }
        }
        return null;
    }

    /**
     * @param Position $chestPos
     *
     * @return BuyShop|null
     */
    public function getBuyShopByChestPos(Position $chestPos): ?BuyShop
    {
        foreach ($this->buyShops as $buyShop) {
            if ($buyShop->getChestPos()->equals($chestPos)) {
                return $buyShop;
            }
        }
        return null;
    }
}
