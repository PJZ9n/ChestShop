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
use pocketmine\level\Position;
use pocketmine\Server;
use SQLite3;

class SQLite3ShopDatabase implements ShopDatabase
{
    /** @var SQLite3 */
    private $db;

    /**
     * @param SQLite3 $db
     */
    public function __construct(SQLite3 $db)
    {
        $this->db = $db;
        $query = <<< EOL
CREATE TABLE IF NOT EXISTS buyshop (
    id INTEGER UNIQUE NOT NULL,
    price INTEGER NOT NULL,
    item_id INTEGER NOT NULL,
    item_meta INTEGER NOT NULL,
    sign_x INTEGER NOT NULL,
    sign_y INTEGER NOT NULL,
    sign_z INTEGER NOT NULL,
    sign_world TEXT NOT NULL,
    chest_x INTEGER NOT NULL,
    chest_y INTEGER NOT NULL,
    chest_z INTEGER NOT NULL,
    chest_world TEXT NOT NULL,
    owner TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS buylog (
    shop_id INTEGER NOT NULL,
    player TEXT NOT NULL,
    log_date TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS sellshop (
    id INTEGER UNIQUE NOT NULL,
    price INTEGER NOT NULL,
    sell_limit INTEGER NOT NULL,
    item_id INTEGER NOT NULL,
    item_meta INTEGER NOT NULL,
    sign_x INTEGER NOT NULL,
    sign_y INTEGER NOT NULL,
    sign_z INTEGER NOT NULL,
    sign_world TEXT NOT NULL,
    chest_x INTEGER NOT NULL,
    chest_y INTEGER NOT NULL,
    chest_z INTEGER NOT NULL,
    chest_world TEXT NOT NULL,
    owner TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS selllog (
    shop_id INTEGER NOT NULL,
    player TEXT NOT NULL,
    log_date TEXT NOT NULL
);
EOL;
        $this->db->exec($query);
    }

    public function close(): void
    {
        $this->db->close();
    }

    public function getAllBuyShop(): array
    {
        $result = $this->db->query(
            "SELECT * FROM buyshop;"
        );
        $shops = [];
        while ($record = $result->fetchArray()) {
            Server::getInstance()->loadLevel($record["sign_world"]);
            $signPosLevel = Server::getInstance()->getLevelByName($record["sign_world"]);
            Server::getInstance()->loadLevel($record["chest_world"]);
            $chestPosLevel = Server::getInstance()->getLevelByName($record["chest_world"]);
            $shops[] = new BuyShop(
                $record["id"],
                $record["price"],
                $record["item_id"],
                $record["item_meta"],
                new Position($record["sign_x"], $record["sign_y"], $record["sign_z"], $signPosLevel),
                new Position($record["chest_x"], $record["chest_y"], $record["chest_z"], $chestPosLevel),
                $record["owner"]
            );
        }
        return $shops;
    }

    public function saveBuyShop(BuyShop $shop): void
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM buyshop WHERE id = :id;"
        );
        $stmt->bindValue(":id", $shop->getId(), SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray();
        if ($result === false) {
            //new
            $query = <<< EOL
INSERT INTO buyshop (
    id, price, item_id, item_meta, sign_x, sign_y, sign_z, sign_world, chest_x, chest_y, chest_z, chest_world, owner
)
VALUES (
    :id, :price, :item_id, :item_meta, :sign_x, :sign_y, :sign_z, :sign_world, :chest_x, :chest_y, :chest_z, :chest_world, :owner
);
EOL;
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":id", $shop->getId(), SQLITE3_INTEGER);
            $stmt->bindValue(":price", $shop->getPrice(), SQLITE3_INTEGER);
            $stmt->bindValue(":item_id", $shop->getItemId(), SQLITE3_INTEGER);
            $stmt->bindValue(":item_meta", $shop->getItemMeta(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_x", $shop->getSignPos()->getX(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_y", $shop->getSignPos()->getY(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_z", $shop->getSignPos()->getZ(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_world", $shop->getSignPos()->getLevel()->getName(), SQLITE3_TEXT);
            $stmt->bindValue(":chest_x", $shop->getChestPos()->getX(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_y", $shop->getChestPos()->getY(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_z", $shop->getChestPos()->getZ(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_world", $shop->getChestPos()->getLevel()->getName(), SQLITE3_TEXT);
            $stmt->bindValue(":owner", $shop->getOwner(), SQLITE3_TEXT);
            $stmt->execute();
        } else {
            //update
            $query = <<< EOL
UPDATE buyshop
SET price = :price, item_id = :item_id, item_meta = :item_meta,
sign_x = :sign_x, sign_y = :sign_y, sign_z = :sign_z, sign_world = :sign_world,
chest_x = :chest_x, chest_y = :chest_y, chest_z = :chest_z, chest_world = :chest_world, owner = :owner
WHERE id = :id;
EOL;
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":id", $shop->getId(), SQLITE3_INTEGER);
            $stmt->bindValue(":price", $shop->getPrice(), SQLITE3_INTEGER);
            $stmt->bindValue(":item_id", $shop->getItemId(), SQLITE3_INTEGER);
            $stmt->bindValue(":item_meta", $shop->getItemMeta(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_x", $shop->getSignPos()->getX(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_y", $shop->getSignPos()->getY(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_z", $shop->getSignPos()->getZ(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_world", $shop->getSignPos()->getLevel()->getName(), SQLITE3_TEXT);
            $stmt->bindValue(":chest_x", $shop->getChestPos()->getX(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_y", $shop->getChestPos()->getY(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_z", $shop->getChestPos()->getZ(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_world", $shop->getChestPos()->getLevel()->getName(), SQLITE3_TEXT);
            $stmt->bindValue(":owner", $shop->getOwner(), SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    public function removeBuyShop(BuyShop $shop): void
    {
        $stmt = $this->db->prepare("DELETE FROM buyshop WHERE id = :id;");
        $stmt->bindValue(":id", $shop->getId(), SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function getAllBuyLog(): array
    {
        $result = $this->db->query(
            "SELECT * FROM buylog;"
        );
        $logs = [];
        while ($record = $result->fetchArray()) {
            $logs[] = new BuyLog($record["shop_id"], $record["player"], date_create($record["log_date"]));
        }
        return $logs;
    }

    public function saveBuyLog(BuyLog $log): void
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM buylog WHERE shop_id = :shop_id;"
        );
        $stmt->bindValue(":shop_id", $log->getShopId(), SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray();
        if ($result === false) {
            //new
            $stmt = $this->db->prepare("INSERT INTO buylog (shop_id, player, log_date) VALUES (:shop_id, :player, :log_date)");
            $stmt->bindValue(":shop_id", $log->getShopId(), SQLITE3_INTEGER);
            $stmt->bindValue(":player", $log->getPlayer(), SQLITE3_TEXT);
            $stmt->bindValue(":log_date", $log->getDate()->format("Y-m-d H:i:s"), SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    public function getAllSellShop(): array
    {
        $result = $this->db->query(
            "SELECT * FROM sellshop;"
        );
        $shops = [];
        while ($record = $result->fetchArray()) {
            Server::getInstance()->loadLevel($record["sign_world"]);
            $signPosLevel = Server::getInstance()->getLevelByName($record["sign_world"]);
            Server::getInstance()->loadLevel($record["chest_world"]);
            $chestPosLevel = Server::getInstance()->getLevelByName($record["chest_world"]);
            $shops[] = new SellShop(
                $record["id"],
                $record["price"],
                $record["item_id"],
                $record["item_meta"],
                new Position($record["sign_x"], $record["sign_y"], $record["sign_z"], $signPosLevel),
                new Position($record["chest_x"], $record["chest_y"], $record["chest_z"], $chestPosLevel),
                $record["sell_limit"],
                $record["owner"]
            );
        }
        return $shops;
    }

    public function saveSellShop(SellShop $shop): void
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM sellshop WHERE id = :id;"
        );
        $stmt->bindValue(":id", $shop->getId(), SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray();
        if ($result === false) {
            //new
            $query = <<< EOL
INSERT INTO sellshop (
    id, price, sell_limit, item_id, item_meta, sign_x, sign_y, sign_z, sign_world, chest_x, chest_y, chest_z, chest_world, owner
)
VALUES (
    :id, :price, :sell_limit, :item_id, :item_meta, :sign_x, :sign_y, :sign_z, :sign_world, :chest_x, :chest_y, :chest_z, :chest_world, :owner
);
EOL;
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":id", $shop->getId(), SQLITE3_INTEGER);
            $stmt->bindValue(":price", $shop->getPrice(), SQLITE3_INTEGER);
            $stmt->bindValue(":sell_limit", $shop->getLimit(), SQLITE3_INTEGER);
            $stmt->bindValue(":item_id", $shop->getItemId(), SQLITE3_INTEGER);
            $stmt->bindValue(":item_meta", $shop->getItemMeta(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_x", $shop->getSignPos()->getX(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_y", $shop->getSignPos()->getY(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_z", $shop->getSignPos()->getZ(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_world", $shop->getSignPos()->getLevel()->getName(), SQLITE3_TEXT);
            $stmt->bindValue(":chest_x", $shop->getChestPos()->getX(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_y", $shop->getChestPos()->getY(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_z", $shop->getChestPos()->getZ(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_world", $shop->getChestPos()->getLevel()->getName(), SQLITE3_TEXT);
            $stmt->bindValue(":owner", $shop->getOwner(), SQLITE3_TEXT);
            $stmt->execute();
        } else {
            //update
            $query = <<< EOL
UPDATE sellshop
SET price = :price, sell_limit = :sell_limit, item_id = :item_id, item_meta = :item_meta,
sign_x = :sign_x, sign_y = :sign_y, sign_z = :sign_z, sign_world = :sign_world,
chest_x = :chest_x, chest_y = :chest_y, chest_z = :chest_z, chest_world = :chest_world, owner = :owner
WHERE id = :id;
EOL;
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":id", $shop->getId(), SQLITE3_INTEGER);
            $stmt->bindValue(":price", $shop->getPrice(), SQLITE3_INTEGER);
            $stmt->bindValue(":sell_limit", $shop->getLimit(), SQLITE3_INTEGER);
            $stmt->bindValue(":item_id", $shop->getItemId(), SQLITE3_INTEGER);
            $stmt->bindValue(":item_meta", $shop->getItemMeta(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_x", $shop->getSignPos()->getX(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_y", $shop->getSignPos()->getY(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_z", $shop->getSignPos()->getZ(), SQLITE3_INTEGER);
            $stmt->bindValue(":sign_world", $shop->getSignPos()->getLevel()->getName(), SQLITE3_TEXT);
            $stmt->bindValue(":chest_x", $shop->getChestPos()->getX(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_y", $shop->getChestPos()->getY(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_z", $shop->getChestPos()->getZ(), SQLITE3_INTEGER);
            $stmt->bindValue(":chest_world", $shop->getChestPos()->getLevel()->getName(), SQLITE3_TEXT);
            $stmt->bindValue(":owner", $shop->getOwner(), SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    public function removeSellShop(SellShop $shop): void
    {
        $stmt = $this->db->prepare("DELETE FROM sellshop WHERE id = :id;");
        $stmt->bindValue(":id", $shop->getId(), SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function getAllSellLog(): array
    {
        $result = $this->db->query(
            "SELECT * FROM selllog;"
        );
        $logs = [];
        while ($record = $result->fetchArray()) {
            $logs[] = new SellLog($record["shop_id"], $record["player"], date_create($record["log_date"]));
        }
        return $logs;
    }

    public function saveSellLog(SellLog $log): void
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM selllog WHERE shop_id = :shop_id;"
        );
        $stmt->bindValue(":shop_id", $log->getShopId(), SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray();
        if ($result === false) {
            $stmt = $this->db->prepare("INSERT INTO selllog (shop_id, player, log_date) VALUES (:shop_id, :player, :log_date)");
            $stmt->bindValue(":shop_id", $log->getShopId(), SQLITE3_INTEGER);
            $stmt->bindValue(":player", $log->getPlayer(), SQLITE3_TEXT);
            $stmt->bindValue(":log_date", $log->getDate()->format("Y-m-d H:i:s"), SQLITE3_TEXT);
            $stmt->execute();
        }
    }
}
