<?php

namespace App\Services;

use App\Models\AdminWalletSetting;
use Illuminate\Support\Facades\Log;
use SWeb3\SWeb3;
use SWeb3\Utils;

class BnbPayoutService
{
  public function isValidBscAddress(?string $address): bool
  {
    if (! $address) {
      return false;
    }

    return (bool) preg_match('/^0x[a-fA-F0-9]{40}$/', trim($address));
  }

  /**
   * @return array{result:mixed,from:string,to:string,amount_bnb:string}
   */
  public function sendBnb(string $toAddress, int $amountBnb): array
  {
    $toAddress = trim($toAddress);
    if (! $this->isValidBscAddress($toAddress)) {
      throw new \InvalidArgumentException('Invalid BSC address');
    }

    if ($amountBnb <= 0) {
      throw new \InvalidArgumentException('Invalid BNB amount');
    }

    $settings = AdminWalletSetting::query()->first();
    if (! $settings || ! $settings->from_address || ! $settings->private_key) {
      throw new \RuntimeException('Missing admin wallet settings');
    }

    $sweb3 = new SWeb3('https://bsc-dataseed1.binance.org/');
    $sweb3->setPersonalData($settings->from_address, $settings->private_key);
    $sweb3->chainId = '56';

    $sendParams = [
      'from' => $sweb3->personal->address,
      'to' => $toAddress,
      'gasLimit' => 210000,
      'value' => Utils::toWei((string) $amountBnb, 'ether'),
      'nonce' => $sweb3->personal->getNonce(),
    ];

    try {
      $result = $sweb3->send($sendParams);

      return [
        'result' => $result,
        'from' => $sweb3->personal->address,
        'to' => $toAddress,
        'amount_bnb' => (string) $amountBnb,
      ];
    } catch (\Throwable $e) {
      Log::error('BNB send failed: ' . $e->getMessage());
      throw $e;
    }
  }
}
