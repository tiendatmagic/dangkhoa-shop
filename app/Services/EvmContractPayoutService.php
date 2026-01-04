<?php

namespace App\Services;

use App\Models\AdminWalletSetting;
use Illuminate\Support\Facades\Log;
use SWeb3\SWeb3;
use SWeb3\SWeb3_Contract;

class EvmContractPayoutService
{
  /**
   * Calls withdrawNative(amountWei, to) on the configured contract.
   *
   * @return mixed Transaction result returned by SWeb3_Contract::send
   */
  public function withdrawNative(int $chainId, string $toAddress, string $amountWei)
  {
    $toAddress = trim($toAddress);
    if (! preg_match('/^0x[a-fA-F0-9]{40}$/', $toAddress)) {
      throw new \InvalidArgumentException('Invalid recipient address');
    }

    $amountWei = trim($amountWei);
    if ($amountWei === '' || ! preg_match('/^[0-9]+$/', $amountWei)) {
      throw new \InvalidArgumentException('Invalid amountWei');
    }

    $settings = AdminWalletSetting::query()->where('chain_id', $chainId)->first();
    if (! $settings) {
      throw new \RuntimeException('Missing wallet settings for chain ' . $chainId);
    }
    if (! $settings->from_address || ! $settings->private_key) {
      throw new \RuntimeException('Missing from_address/private_key');
    }
    if (! $settings->rpc_url) {
      throw new \RuntimeException('Missing rpc_url');
    }
    if (! $settings->contract_address || ! preg_match('/^0x[a-fA-F0-9]{40}$/', $settings->contract_address)) {
      throw new \RuntimeException('Missing/invalid contract_address');
    }

    $abiPath = base_path('app/abi/ABI.json');
    if (! file_exists($abiPath)) {
      throw new \RuntimeException('ABI.json not found');
    }

    $abi = file_get_contents($abiPath);

    $sweb3 = new SWeb3($settings->rpc_url);
    $sweb3->setPersonalData($settings->from_address, $settings->private_key);
    $sweb3->chainId = (string) $chainId;

    $contract = new SWeb3_Contract($sweb3, $settings->contract_address, $abi);

    $extra = [
      'nonce' => $sweb3->personal->getNonce(),
      'gasLimit' => 300000,
    ];

    try {
      return $contract->send('withdrawNative', [$amountWei, $toAddress], $extra);
    } catch (\Throwable $e) {
      Log::error('withdrawNative failed: ' . $e->getMessage());
      throw $e;
    }
  }
}
