<?php

namespace App\Services;

use App\Models\AdminWalletSetting;
use Illuminate\Support\Facades\Log;
use SWeb3\Accounts;
use SWeb3\SWeb3;

class BnbPayoutService
{
  private function normalizePrivateKey(string $privateKey): string
  {
    $privateKey = trim($privateKey);
    if (str_starts_with($privateKey, '0x') || str_starts_with($privateKey, '0X')) {
      $privateKey = substr($privateKey, 2);
    }
    $privateKey = strtolower(trim($privateKey));
    if ($privateKey === '' || strlen($privateKey) !== 64 || ! ctype_xdigit($privateKey)) {
      throw new \RuntimeException('Invalid private_key format (expected 64 hex characters)');
    }
    return $privateKey;
  }

  private function assertPrivateKeyMatchesFromAddress(string $fromAddress, string $privateKey64Hex): void
  {
    $fromAddress = strtolower(trim($fromAddress));
    if (! preg_match('/^0x[a-f0-9]{40}$/', $fromAddress)) {
      throw new \RuntimeException('Invalid from_address');
    }

    $account = Accounts::privateKeyToAccount($privateKey64Hex);
    $derived = strtolower((string) ($account->address ?? ''));
    if ($derived !== $fromAddress) {
      throw new \RuntimeException('private_key does not match from_address (derived: ' . $derived . ')');
    }
  }

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

    $fromAddress = trim((string) $settings->from_address);
    $privateKey = $this->normalizePrivateKey((string) $settings->private_key);
    $this->assertPrivateKeyMatchesFromAddress($fromAddress, $privateKey);

    $sweb3 = new SWeb3('https://bsc-dataseed1.binance.org/');
    $sweb3->setPersonalData($fromAddress, $privateKey);
    $sweb3->chainId = '56';

    // Avoid Utils::toWei() to prevent any pow()/bcmath/gmp fallback issues.
    // Amount is in whole BNB; value must be wei as an integer string.
    $amountWei = (string) (app(PriceQuoteService::class)->decimalToUnits((string) $amountBnb, 18));

    $sendParams = [
      'from' => $sweb3->personal->address,
      'to' => $toAddress,
      'gasLimit' => 210000,
      'value' => $amountWei,
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
