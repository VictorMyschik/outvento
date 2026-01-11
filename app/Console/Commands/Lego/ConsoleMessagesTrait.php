<?php

namespace App\Console\Commands\Lego;

/**
 * Trait of Whoosh colored output to console.
 *
 * @package Whoosh\Traits
 * @category Whoosh Traits
 * @version 1.0
 */
trait ConsoleMessagesTrait
{
  /**
   * Sends success message to command line output.
   * @param string $message Message to send.
   */
  public function success($message): void
  {
    echo "\033[32m" . $message . "\033[0m";
  }

  /**
   * Sends warning message to command line output.
   * @param string $message Message to send.
   */
  public function warning($message): void
  {
    echo "\033[33m" . $message . "\033[0m";
  }

  /**
   * Sends error message to command line output.
   * @param string $message Message to send.
   */
  public function err($message): void
  {
    echo "\033[31m" . $message . "\033[0m";
  }

  /**
   * Sends resource name message to command line output.
   * @param $message
   * @param $returnValue
   * @return string|null
   */
  public function printResourceName($message, $returnValue = false): ?string
  {
    $response = " \033[32m" . $message . "\033[0m";
    if($returnValue === true) {
      return $response;
    }

    echo $response;

    return null;
  }

  /**
   * Sends end of line to command line output.
   */
  public function nl(): void
  {
    echo PHP_EOL;
  }

  /**
   * returns information about resources spent during script execution
   * @param float $startTime
   * @return string
   */
  public function getStatisticsMessage(float $startTime): string
  {
    $memoryUsage = memory_get_peak_usage() / 1024 / 1024;
    $memoryUsage = round($memoryUsage, 2);
    $message = "\nMemory peak: {$memoryUsage}MB";
    $endTime = microtime(1);
    $message .= "\nTime spent: " . gmdate("H:i:s", $endTime - $startTime);
    $message .= "\n";

    return $message;
  }
}
