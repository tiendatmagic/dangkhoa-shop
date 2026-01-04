<?php

namespace App\Services;

use App\Models\AdminWalletSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SWeb3\Accounts;
use SWeb3\SWeb3;
use SWeb3\SWeb3_Contract;

class EvmContractPayoutService
{
  /**
   * Returns a normalized private key as 64 hex characters (no 0x prefix).
   */
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

  /**
   * Ensures the private key corresponds to the configured from address.
   */
  private function assertPrivateKeyMatchesFromAddress(string $fromAddress, string $privateKey64Hex): void
  {
    $fromAddress = strtolower(trim($fromAddress));
    if (! preg_match('/^0x[a-f0-9]{40}$/', $fromAddress)) {
      throw new \RuntimeException('Invalid from_address');
    }

    try {
      $account = Accounts::privateKeyToAccount($privateKey64Hex);
      $derived = strtolower((string) ($account->address ?? ''));
      if ($derived === '' || ! preg_match('/^0x[a-f0-9]{40}$/', $derived)) {
        throw new \RuntimeException('Could not derive address from private_key');
      }

      if ($derived !== $fromAddress) {
        throw new \RuntimeException('private_key does not match from_address (derived: ' . $derived . ')');
      }
    } catch (\Throwable $e) {
      throw new \RuntimeException('Invalid private_key: ' . $e->getMessage());
    }
  }

  private function assertRpcUrlLooksValid(string $rpcUrl): void
  {
    $rpcUrl = trim($rpcUrl);
    if ($rpcUrl === '') {
      throw new \RuntimeException('Missing rpc_url');
    }

    // Common misconfig: Infura base URL without /v3/<project_id>
    if (preg_match('#^https?://bsc-mainnet\.infura\.io/?$#i', $rpcUrl)) {
      throw new \RuntimeException('Invalid rpc_url: Infura URL is missing /v3/<project_id>');
    }
  }

  /**
   * Returns nonce as an integer for the given address at "pending" state.
   */
  private function fetchPendingNonce(string $rpcUrl, string $fromAddress): int
  {
    $this->assertRpcUrlLooksValid($rpcUrl);

    $fromAddress = trim($fromAddress);
    if (! preg_match('/^0x[a-fA-F0-9]{40}$/', $fromAddress)) {
      throw new \RuntimeException('Invalid from_address');
    }

    $resp = Http::timeout(15)->acceptJson()->post($rpcUrl, [
      'jsonrpc' => '2.0',
      'id' => 1,
      'method' => 'eth_getTransactionCount',
      'params' => [$fromAddress, 'pending'],
    ]);

    if (! $resp->successful()) {
      throw new \RuntimeException('RPC nonce request failed: HTTP ' . $resp->status() . ' ' . (string) $resp->body());
    }

    $json = $resp->json();
    if (isset($json['error'])) {
      $msg = is_array($json['error']) ? ($json['error']['message'] ?? json_encode($json['error'])) : (string) $json['error'];
      throw new \RuntimeException('RPC nonce error: ' . $msg);
    }

    $hex = (string) ($json['result'] ?? '');
    if ($hex === '' || ! str_starts_with($hex, '0x')) {
      throw new \RuntimeException('RPC nonce response missing result');
    }

    $hex = substr($hex, 2);
    if ($hex === '') {
      return 0;
    }

    // nonce is small, but use GMP when available.
    if (function_exists('gmp_init')) {
      return (int) gmp_strval(gmp_init($hex, 16));
    }

    return (int) hexdec($hex);
  }

  /**
   * Calls withdrawERC20(token, amountWei, to) on the configured contract.
   *
   * @return mixed Transaction result returned by SWeb3_Contract::send
   */
  public function withdrawErc20(int $chainId, string $tokenAddress, string $toAddress, string $amountWei)
  {
    $tokenAddress = trim($tokenAddress);
    if (! preg_match('/^0x[a-fA-F0-9]{40}$/', $tokenAddress)) {
      throw new \InvalidArgumentException('Invalid token address');
    }

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
    $this->assertRpcUrlLooksValid($settings->rpc_url);
    if (! $settings->contract_address || ! preg_match('/^0x[a-fA-F0-9]{40}$/', $settings->contract_address)) {
      throw new \RuntimeException('Missing/invalid contract_address');
    }

    $abiPath = base_path('app/abi/ABI.json');
    if (! file_exists($abiPath)) {
      throw new \RuntimeException('ABI.json not found');
    }

    $abi = file_get_contents($abiPath);

    $fromAddress = trim((string) $settings->from_address);
    $privateKey = $this->normalizePrivateKey((string) $settings->private_key);
    $this->assertPrivateKeyMatchesFromAddress($fromAddress, $privateKey);

    $sweb3 = new SWeb3($settings->rpc_url);
    // Use normalized private key (no 0x) to avoid signing errors.
    $sweb3->setPersonalData($fromAddress, $privateKey);
    $sweb3->chainId = (string) $chainId;

    $contract = new SWeb3_Contract($sweb3, $settings->contract_address, $abi);

    $extra = [
      'nonce' => $this->fetchPendingNonce($settings->rpc_url, $fromAddress),
      'gasLimit' => 300000,
    ];

    try {
      return $contract->send('withdrawERC20', [$tokenAddress, $amountWei, $toAddress], $extra);
    } catch (\Throwable $e) {
      Log::error('withdrawERC20 failed: ' . $e->getMessage());
      throw $e;
    }
  }

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
    $this->assertRpcUrlLooksValid($settings->rpc_url);
    if (! $settings->contract_address || ! preg_match('/^0x[a-fA-F0-9]{40}$/', $settings->contract_address)) {
      throw new \RuntimeException('Missing/invalid contract_address');
    }

    $abiPath = base_path('app/abi/ABI.json');
    if (! file_exists($abiPath)) {
      throw new \RuntimeException('ABI.json not found');
    }

    $abi = file_get_contents($abiPath);

    $fromAddress = trim((string) $settings->from_address);
    $privateKey = $this->normalizePrivateKey((string) $settings->private_key);
    $this->assertPrivateKeyMatchesFromAddress($fromAddress, $privateKey);

    $sweb3 = new SWeb3($settings->rpc_url);
    // Use normalized private key (no 0x) to avoid signing errors.
    $sweb3->setPersonalData($fromAddress, $privateKey);
    $sweb3->chainId = (string) $chainId;

    $contract = new SWeb3_Contract($sweb3, $settings->contract_address, $abi);

    $extra = [
      'nonce' => $this->fetchPendingNonce($settings->rpc_url, $fromAddress),
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
