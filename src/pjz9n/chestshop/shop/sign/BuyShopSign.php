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

namespace pjz9n\chestshop\shop\sign;

use Particle\Validator\Validator;
use pocketmine\item\ItemFactory;
use pocketmine\tile\Sign;
use InvalidArgumentException;

class BuyShopSign extends ShopSign
{
    public static function fromSignTile(Sign $sign): BuyShopSign
    {
        $signContent = $sign->getText();
        $v = new Validator();
        //price
        $v->required(0)->integer()->between(1, PHP_INT_MAX);
        //item
        $v->required(1)->string()->callback(function ($value) {
            try {
                $item = ItemFactory::fromString($value);
                if (is_array($item)) {
                    return false;
                }
            } catch (InvalidArgumentException $invalidArgumentException) {
                return false;
            }
            return true;
        });
        $r = $v->validate($signContent);
        if ($r->isNotValid()) throw new InvalidSignSyntaxException();
        $values = $r->getValues();
        $item = ItemFactory::fromString($values[1]);
        return new BuyShopSign($sign, (int)$values[0], $item->getId(), $item->getDamage());
    }

    public function writeSign(string $owner): void
    {
        //TODO: Language support
        $this->getSignTile()->setText(
            "[buy]",
            $owner,
            "Price: " . (string)$this->getPrice(),
            "Item: " . (string)$this->getItemId() . ":" . (string)$this->getItemMeta()
        );
    }
}
