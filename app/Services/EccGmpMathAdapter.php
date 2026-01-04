<?php

namespace App\Services;

use Mdanter\Ecc\Math\GmpMath;

/**
 * Workaround for PHP GMP shift/pow overflow.
 *
 * Production error seen:
 * ValueError: base and exponent overflow @ vendor/mdanter/ecc/src/Math/GmpMath.php
 *
 * The stock adapter implements shifts via gmp_pow(2, $positions). On some
 * environments that can throw. We replace shift ops with gmp_*_2exp.
 */
class EccGmpMathAdapter extends GmpMath
{
  public const VERSION = '2026-01-04-pow-override';

  public function pow(\GMP $base, int $exponent): \GMP
  {
    if ($exponent < 0) {
      throw new \InvalidArgumentException('Negative exponent not supported');
    }

    // Avoid pathological runtime / memory use.
    if ($exponent > 65536) {
      throw new \RuntimeException('Exponent too large');
    }

    $result = \gmp_init(1, 10);
    $power = $base;
    $e = $exponent;

    while ($e > 0) {
      if (($e & 1) === 1) {
        $result = \gmp_mul($result, $power);
      }
      $e = $e >> 1;
      if ($e > 0) {
        $power = \gmp_mul($power, $power);
      }
    }

    return $result;
  }

  public function rightShift(\GMP $number, int $positions): \GMP
  {
    if ($positions <= 0) {
      return $number;
    }

    // Avoid gmp_pow() (can throw "base and exponent overflow" on some hosts)
    // and avoid relying on gmp_*_2exp helpers (not always available).
    if ($positions > 4096) {
      throw new \RuntimeException('Shift positions too large');
    }

    $two = \gmp_init(2, 10);
    for ($i = 0; $i < $positions; $i++) {
      if (function_exists('gmp_div_q')) {
        $number = \gmp_div_q($number, $two);
      } else {
        $number = \gmp_div($number, $two);
      }
    }

    return $number;
  }

  public function leftShift(\GMP $number, int $positions): \GMP
  {
    if ($positions <= 0) {
      return $number;
    }

    if ($positions > 4096) {
      throw new \RuntimeException('Shift positions too large');
    }

    $two = \gmp_init(2, 10);
    for ($i = 0; $i < $positions; $i++) {
      $number = \gmp_mul($number, $two);
    }

    return $number;
  }
}