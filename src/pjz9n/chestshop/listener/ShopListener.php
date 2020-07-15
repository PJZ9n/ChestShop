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

namespace pjz9n\chestshop\listener;

use pjz9n\chestshop\shop\BuyShop;
use pjz9n\chestshop\ShopManager;
use PJZ9n\MoneyConnector\MoneyConnector;
use pocketmine\block\Chest;
use pocketmine\block\SignPost;
use pocketmine\tile\Chest as ChestTile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\lang\BaseLang;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

//TODO: FIX THIS FUCKING CODE...
class ShopListener implements Listener
{
    /** @var BaseLang */
    private $lang;

    /** @var MoneyConnector */
    private $money;

    /** @var ShopManager */
    private $shopManager;

    /**
     * @param BaseLang $lang
     * @param MoneyConnector $money
     * @param ShopManager $shopManager
     */
    public function __construct(BaseLang $lang, MoneyConnector $money, ShopManager $shopManager)
    {
        $this->lang = $lang;
        $this->money = $money;
        $this->shopManager = $shopManager;
    }

    public function checkShopSign(PlayerInteractEvent $event): void
    {
        if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
        $block = $event->getBlock();
        if (!($block instanceof SignPost)) return;
        $buyShop = $this->shopManager->getBuyShopBySignPos($block->asPosition());
        if (!($buyShop instanceof BuyShop)) return;
        $player = $event->getPlayer();
        $name = $player->getName();
        if ($buyShop->getOwner() === $name) {
            $player->sendMessage($this->lang->translateString(TextFormat::RED . "%shop.owned"));
            return;
        }
        $chestPos = $buyShop->getChestPos();
        $chestTile = $chestPos->getLevel()->getTile($chestPos);
        if (!($chestTile instanceof ChestTile)) return;
        $chestInventory = $chestTile->getInventory();
        $buyItem = $buyShop->getItem();
        if (!$chestInventory->contains($buyItem)) {
            $player->sendMessage($this->lang->translateString(TextFormat::RED . "%shop.outofstock"));
            return;
        }
        $playerInventory = $player->getInventory();
        if (!$playerInventory->canAddItem($buyItem)) {
            $player->sendMessage($this->lang->translateString(TextFormat::RED . "%player.inventory.nospace"));
            return;
        }
        $price = $buyShop->getPrice();
        if ($this->money->myMoney($player) - $price < 0) {
            $player->sendMessage($this->lang->translateString(TextFormat::RED . "%player.pay.noenough"));
            return;
        }
        if ($this->money->reduceMoney($player, $price) !== MoneyConnector::RETURN_SUCCESS) {
            $player->sendMessage($this->lang->translateString(TextFormat::RED . "%player.pay.failed"));
            return;
        }
        $chestInventory->removeItem($buyItem);
        $playerInventory->addItem($buyItem);
        $this->money->addMoneyByName($buyShop->getOwner(), $price);
        $player->sendMessage($this->lang->translateString(
            TextFormat::GOLD . "%shop.buy", [$price, $buyShop->getItem()->__toString()]
        ));
    }

    public function checkShopTouchProtection(PlayerInteractEvent $event): void
    {
        $block = $event->getBlock();
        if (!($block instanceof Chest)) return;
        $buyShop = $this->shopManager->getBuyShopByChestPos($block->asPosition());
        if (!($buyShop instanceof BuyShop)) {
            $tile = $tile = $block->getLevel()->getTile($block);
            if (!($tile instanceof ChestTile)) return;
            if (!$tile->isPaired()) return;
            $buyShop = $this->shopManager->getBuyShopByChestPos($tile->getPair());
            if (!($buyShop instanceof BuyShop)) {
                return;
            }
        }
        $player = $event->getPlayer();
        if (!$this->hasEditPermission($buyShop, $player)) {
            $event->setCancelled(true);
            $player->sendMessage($this->lang->translateString(TextFormat::RED . "%shop.notpermission"));
        }
    }

    public function checkShopBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        if (!($block instanceof Chest) && !($block instanceof SignPost)) return;
        $buyShop = $this->shopManager->getBuyShopByChestPos($block->asPosition());
        if (!($buyShop instanceof BuyShop)) {
            $buyShop = $this->shopManager->getBuyShopBySignPos($block->asPosition());
            if (!($buyShop instanceof BuyShop)) {
                $tile = $tile = $block->getLevel()->getTile($block);
                if (!($tile instanceof ChestTile)) return;
                if (!$tile->isPaired()) return;
                $buyShop = $this->shopManager->getBuyShopByChestPos($tile->getPair());
                if (!($buyShop instanceof BuyShop)) {
                    return;
                }
            }
        }
        $player = $event->getPlayer();
        if (!$this->hasEditPermission($buyShop, $player)) {
            $event->setCancelled(true);
            $player->sendMessage($this->lang->translateString(TextFormat::RED . "%shop.notpermission"));
            return;
        }
        $this->shopManager->removeBuyShop($buyShop->getId());
        $player->sendMessage($this->lang->translateString(TextFormat::GOLD . "%shop.removed"));
    }

    private function hasEditPermission(BuyShop $shop, Player $player): bool
    {
        $name = $player->getName();
        if (!($player->hasPermission("chestshop.bypass.shop.protection")) && $shop->getOwner() !== $name) {
            return false;
        }
        return true;
    }
}
