<?php
namespace verbb\shippy\models;

use DVDoug\BoxPacker\ItemList;
use DVDoug\BoxPacker\VolumePacker;
use Illuminate\Support\Arr;
use verbb\shippy\carriers\CarrierInterface;

class StaticRates
{
    // Properties
    // =========================================================================

    public static function getRateFromBoxRates(Shipment $shipment, CarrierInterface $carrier, array $boxRates, string $serviceCode): ?Rate
    {
        $rate = 0;
        $currency = null;

        // Ensure we sort boxes now by price, so we can stop at the first (cheapest) one.
        uasort($boxRates, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });

        // For each package, find the cheapest box that fits. We'll use that price for the rate for each package.
        foreach ($shipment->getPackages() as $package) {
            foreach ($boxRates as $name => $boxRate) {
                $box = new PackageBox([
                    'reference' => $name,
                    'outerWidth' => $boxRate['width'],
                    'outerLength' => $boxRate['length'],
                    'outerDepth' => $boxRate['height'],
                    'emptyWeight' => 0,
                    'innerWidth' => $boxRate['width'],
                    'innerLength' => $boxRate['length'],
                    'innerDepth' => $boxRate['height'],
                    'maxWeight' => $boxRate['weight'],
                    'price' => $boxRate['price'],
                    'currency' => $boxRate['currency'] ?? null,
                    'maxItemValue' => $boxRate['itemValue'] ?? null,
                ]);

                // Allow the boxes currency to set the overall rate currency
                $currency = $boxRate['currency'] ?? null;

                $items = new ItemList();

                $items->insert(new PackageItem([
                    'width' => $package->getWidth(),
                    'length' => $package->getLength(),
                    'depth' => $package->getHeight(),
                    'weight' => $package->getWeight(),
                    'itemValue' => $package->getPrice(),
                    'keepFlat' => false,
                ]));

                $volumePacker = new VolumePacker($box, $items);
                $packedBox = $volumePacker->pack();

                if ($packedBox->getItems()->count()) {
                    // Accumulate the price of each box from the carrier to handle multiple packages.
                    $rate += $packedBox->getBox()->getPrice();

                    // Quit looking through boxes that are suitable, we've got one.
                    break;
                }
            }
        }

        if (!$rate) {
            return null;
        }

        return new Rate([
            'carrier' => $carrier,
            'serviceName' => Arr::get($carrier::getServiceCodes(), $serviceCode, ''),
            'serviceCode' => $serviceCode,
            'rate' => $rate,
            'currency' => $currency,
        ]);
    }

}
