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

namespace pjz9n\chestshop\database;

use pjz9n\chestshop\log\BuyLog;
use pjz9n\chestshop\log\SellLog;
use pjz9n\chestshop\shop\BuyShop;
use pjz9n\chestshop\shop\SellShop;

interface ShopDatabase
{
    /**
     * close the database
     */
    public function close(): void;

    /**
     * get all BuyShop
     *
     * @return BuyShop[]
     */
    public function getAllBuyShop(): array;

    /**
     * save BuyShop
     *
     * @param BuyShop $shop
     */
    public function saveBuyShop(BuyShop $shop): void;

    /**
     * get all buy log
     *
     * @return BuyLog[]
     */
    public function getAllBuyLog(): array;

    /**
     * save buy log
     *
     * @param BuyLog $log
     */
    public function saveBuyLog(BuyLog $log): void;

    /**
     * get all SellShop
     *
     * @return SellShop[]
     */
    public function getAllSellShop(): array;

    /**
     * save SellShop
     *
     * @param SellShop $shop
     */
    public function saveSellShop(SellShop $shop): void;

    /**
     * get all sell log
     *
     * @return SellLog[]
     */
    public function getAllSellLog(): array;

    /**
     * save sell log
     *
     * @param SellLog $log
     */
    public function saveSellLog(SellLog $log): void;
}
