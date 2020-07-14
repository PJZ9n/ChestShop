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

use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use flowy\Flowy;
use pjz9n\chestshop\database\SQLite3ShopDatabase;
use PJZ9n\MoneyConnector\MoneyConnector;
use PJZ9n\MoneyConnector\MoneyConnectorUtils;
use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use RuntimeException;
use SQLite3;

class Main extends PluginBase
{
    /** @var BaseLang */
    private $lang;

    /** @var MoneyConnector */
    private $money;

    /** @var ShopManager */
    private $shopManager;

    /**
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void
    {
        //config
        new Config($this->getDataFolder() . "config.yml", Config::YAML, [
            "lang" => "default",
            "money-api" => "EconomyAPI",
        ]);
        //lang
        $configLang = (string)$this->getConfig()->get("lang", "default");
        $lang = $configLang === "default" ? $this->getServer()->getLanguage()->getLang() : $configLang;
        $localePath = $this->getFile() . "resources/locale/";
        $this->lang = new BaseLang($lang, $localePath, "eng");
        $this->getLogger()->info($this->lang->translateString("language.selected", [$this->lang->getName()]));
        //money
        $configMoney = (string)$this->getConfig()->get("money-api");
        $money = MoneyConnectorUtils::getConnectorByName($configMoney);
        if (!($money instanceof MoneyConnector)) {
            throw new RuntimeException("Unsupported API: " . $configMoney);
        }
        $this->money = $money;
        $this->getLogger()->info($this->lang->translateString("money.selected", [$configMoney]));
        //commando
        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        //flowy
        Flowy::bootstrap();
        //shopmanager
        $dbPath = $this->getDataFolder() . "shop.sqlite";
        if (!file_exists($dbPath)) {
            $flags = SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE;
        } else {
            $flags = SQLITE3_OPEN_READWRITE;
        }
        $db = new SQLite3($dbPath, $flags);
        $this->shopManager = new ShopManager(new SQLite3ShopDatabase($db));
    }
}
