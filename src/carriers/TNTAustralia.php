<?php
namespace verbb\shippy\carriers;

use DateTime;
use Exception;
use Illuminate\Support\Arr;
use verbb\shippy\exceptions\InvalidRequestException;
use verbb\shippy\helpers\Xml;
use verbb\shippy\models\HttpClient;
use verbb\shippy\models\LabelResponse;
use verbb\shippy\models\Rate;
use verbb\shippy\models\RateResponse;
use verbb\shippy\models\Request;
use verbb\shippy\models\Response;
use verbb\shippy\models\Shipment;
use verbb\shippy\models\TrackingResponse;

class TNTAustralia extends AbstractCarrier
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'TNT Australia';
    }

    public static function getWeightUnit(Shipment $shipment): string
    {
        return 'kg';
    }

    public static function getDimensionUnit(Shipment $shipment): string
    {
        return 'cm';
    }

    public static function getServiceCodes(): array
    {
        return [
            'EX10' => '10:00 Express',
            'EX12' => '12:00 Express',
            '712' => '9:00 Express',
            '717' => 'Sensitive Express',
            '717B' => 'Sensitive Express',
            '73' => 'Overnight PAYU Satchel',
            '75' => 'Overnight Express',
            '76' => 'Road Express',
            '718' => 'Fashion Express',
            '701' => 'National Same day',
        ];
    }

    public static function supportsTrackingStatus(): bool
    {
        return false;
    }

    public static function supportsLabels(): bool
    {
        return false;
    }


    // Properties
    // =========================================================================

    protected ?string $accountNumber = null;
    protected ?string $username = null;
    protected ?string $password = null;


    // Public Methods
    // =========================================================================

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): TNTAustralia
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): TNTAustralia
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): TNTAustralia
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @throws InvalidRequestException
     * @throws Exception
     */
    public function getRates(Shipment $shipment): ?RateResponse
    {
        $this->validate('accountNumber', 'username', 'password');

        $nextDate = $this->_numberOfWorkingDates(date('Y-m-d'), 1);

        $payload = [
            'cutOffTimeEnquiry' => [
                'collectionAddress' => [
                    'suburb' => $shipment->getFrom()->getCity(),
                    'postCode' => $shipment->getFrom()->getPostalCode(),
                    'state' => $shipment->getFrom()->getStateProvince(),
                ],
                'deliveryAddress' => [
                    'suburb' => $shipment->getTo()->getCity(),
                    'postCode' => $shipment->getTo()->getPostalCode(),
                    'state' => $shipment->getTo()->getStateProvince(),
                ],
                'shippingDate' => $nextDate[0],
                'userCurrentLocalDateTime' => date('Y-m-d\TH:i:s'),
                'dangerousGoods' => [
                    'dangerous' => false,
                ],
                'packageLines' => [
                    '@packageType' => 'N',
                ],
            ],
            'termsOfPayment' => [
                'senderAccount' => $this->accountNumber,
                'payer' => 'S',
            ],
        ];

        foreach ($shipment->getPackages() as $package) {
            $payload['cutOffTimeEnquiry']['packageLines']['packageLine'][] = [
                'numberOfPackages' => 1,
                'dimensions' => [
                    '@unit' => self::getDimensionUnit($shipment),
                    'length' => $package->getLength(0),
                    'width' => $package->getWidth(0),
                    'height' => $package->getHeight(0),
                ],
                'weight' => [
                    '@unit' => self::getWeightUnit($shipment),
                    'weight' => $package->getWeight(0),
                ],
            ];
        }

        $xml = Xml::encode([
            '@xmlns' => 'http://www.tntexpress.com.au',
            'ratedTransitTimeEnquiry' => $payload,
        ], [
            'xml_root_node_name' => 'enquiry',
        ]);

        $request = new Request([
            'endpoint' => 'Rtt/inputRequest.asp',
            'payload' => [
                'form_params' => [
                    'Username' => $this->username,
                    'Password' => $this->password,
                    'XMLRequest' => $xml,
                ],
            ],
        ]);

        $data = $this->fetchRates($request, function(Response $response) {
            return $response->xml();
        });

        $rates = [];

        foreach (Arr::get($data, 'ratedTransitTimeResponse.ratedProducts.ratedProduct', []) as $service) {
            $serviceCode = Arr::get($service, 'product.code');
            $serviceName = Arr::get($service, 'product.description');
            $rate = Arr::get($service, 'quote.price');
            $currency = Arr::get($service, 'quote.@currency');

            $rates[] = new Rate([
                'carrier' => $this,
                'response' => $service,
                'serviceName' => $serviceName,
                'serviceCode' => $serviceCode,
                'rate' => $rate,
                'currency' => $currency,
            ]);
        }

        return new RateResponse([
            'response' => $data,
            'rates' => $rates,
        ]);
    }

    /**
     * @throws Exception
     */
    public function getTrackingStatus(array $trackingNumbers, array $options = []): ?TrackingResponse
    {
        throw new Exception('Not implemented.');
    }

    /**
     * @throws Exception
     */
    public function getLabels(Shipment $shipment, Rate $rate, array $options = []): ?LabelResponse
    {
        throw new Exception('Not implemented.');
    }

    public function getHttpClient(): HttpClient
    {
        return new HttpClient([
            'base_uri' => 'https://www.tntexpress.com.au',
            'headers' => [
                'Content-Type' => 'application/xml',
            ],
        ]);
    }


    // Private Methods
    // =========================================================================

    /**
     * @throws Exception
     */
    private function _numberOfWorkingDates($from, $days): array
    {
        $workingDays = [1, 2, 3, 4, 5];
        $holidayDays = ['*-12-25', '*-12-26', '*-12-27', '*-12-28', '*-12-29', '*-12-30', '*-12-31', '*-01-01', '*-01-02', '*-01-03', '*-01-04', '*-01-05', '*-01-26'];

        $from = new DateTime($from);
        $dates = [];

        while ($days) {
            $from->modify('+1 day');

            if (!in_array($from->format('N'), $workingDays)) {
                continue;
            }

            if (in_array($from->format('Y-m-d'), $holidayDays)) {
                continue;
            }

            if (in_array($from->format('*-m-d'), $holidayDays)) {
                continue;
            }

            $dates[] = $from->format('Y-m-d');
            $days--;
        }

        return $dates;
    }

}
