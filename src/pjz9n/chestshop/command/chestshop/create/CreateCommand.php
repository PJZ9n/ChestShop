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

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pjz9n\chestshop\flow\Flow;
use pjz9n\chestshop\shop\BuyShop;
use pjz9n\chestshop\shop\sign\BuyShopSign;
use pjz9n\chestshop\shop\sign\InvalidSignSyntaxException;
use pjz9n\chestshop\ShopManager;
use pocketmine\block\Chest;
use pocketmine\block\SignPost;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\lang\BaseLang;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use RuntimeException;
use function flowy\start;

class CreateCommand extends BaseSubCommand
{
    /** @var BaseLang */
    private $lang;

    /** @var ShopManager */
    private $shopManager;

    public function __construct(BaseLang $lang, ShopManager $shopManager)
    {
        $this->lang = $lang;
        $this->shopManager = $shopManager;
        parent::__construct(
            "create",
            $this->lang->translateString("command.chestshop.create.description"),
            ["c"]
        );
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("chestshop.command.chestshop.create");
        $this->registerArgument(0, new ShopTypeArgument("shopType"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage($this->lang->translateString(TextFormat::RED . "%command.onlyplayer"));
            return;
        }
        switch ($args["shopType"]) {
            case "buy":
                start($this->getPlugin())->run(function ($stream) use ($sender) {
                    $chestBlock = null;
                    $signBlock = null;
                    //Select chest
                    while (true) {
                        $sender->sendMessage($this->lang->translateString(TextFormat::GOLD . "%select.chest"));
                        /** @var PlayerInteractEvent $event */
                        $event = yield Flow::generalClickBlockListen($sender);
                        $event->setCancelled();
                        $block = $event->getBlock();
                        if (!($block instanceof Chest)) {
                            $sender->sendMessage($this->lang->translateString(TextFormat::RED . "%select.invalid"));
                            continue;
                        }
                        $sender->sendMessage($this->lang->translateString(TextFormat::YELLOW . "%select.success"));
                        $chestBlock = $block;
                        break;
                    }
                    //Select sign
                    while (true) {
                        $sender->sendMessage($this->lang->translateString(TextFormat::GOLD . "%select.sign"));
                        /** @var PlayerInteractEvent $event */
                        $event = yield Flow::generalClickBlockListen($sender);
                        $event->setCancelled();
                        $block = $event->getBlock();
                        if (!($block instanceof SignPost)) {
                            $sender->sendMessage($this->lang->translateString(TextFormat::RED . "%select.invalid"));
                            continue;
                        }
                        $sender->sendMessage($this->lang->translateString(TextFormat::YELLOW . "%select.success"));
                        $signBlock = $block;
                        break;
                    }
                    //Check exists
                    $buyShop = $this->shopManager->getBuyShopByChestPos($chestBlock);
                    if ($buyShop instanceof BuyShop) {
                        $sender->sendMessage($this->lang->translateString(TextFormat::RED . "%shop.already.exists"));
                        return;
                    }
                    $buyShop = $this->shopManager->getBuyShopBySignPos($signBlock);
                    if ($buyShop instanceof BuyShop) {
                        $sender->sendMessage($this->lang->translateString(TextFormat::RED . "%shop.already.exists"));
                        return;
                    }
                    //Check sign
                    $signTile = $signBlock->getLevel()->getTile($signBlock);
                    if (!($signTile instanceof Sign)) {
                        throw new RuntimeException("Excepted " . Sign::class . ", got " . get_class($signTile));
                    }
                    try {
                        $buySign = BuyShopSign::fromSignTile($signTile);
                    } catcH (InvalidSignSyntaxException $invalidSignSyntaxException) {
                        $sender->sendMessage($this->lang->translateString(TextFormat::RED . "%sign.format.invalid"));
                        return;
                    }
                    //Add
                    $this->shopManager->addBuyShop($buySign->getPrice(), $buySign->getItem(), $signBlock, $chestBlock, $sender);
                    $buySign->writeSign($sender->getName());
                    $sender->sendMessage($this->lang->translateString(TextFormat::GOLD . "%shop.created"));
                });
                return;
        }
    }
}
