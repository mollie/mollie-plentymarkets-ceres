<?php

namespace Mollie\Traits;

/**
 * Trait CanCorrectAmountDifferences
 * @package Mollie\Traits
 *
 */
trait CanCorrectAmountDifferences
{
    /**
     * Unfortunately we have to determine smaller differences between amounts which
     * plentymarkets has calculated and the amounts which mollie is expecting.
     *
     * This is strongly depending on the system configuration at"
     * Setup > Client > [client] > Locations > [location] > Accounting > "Number of decimal places" & "Round totals only"
     *
     * @param float|string $expectedTotalAmount
     * @param float|string $currentTotalAmount
     * @param float|string $originalFirstLineAMount
     * @return string
     */
    private function correctAmount($expectedTotalAmount, $currentTotalAmount, $originalFirstLineAMount)
    {
        if ($expectedTotalAmount != $currentTotalAmount) {
            $diff = $expectedTotalAmount - $currentTotalAmount;

            //automatic amount correction only for a maximum of 3 cents difference
            if ($diff <= 0.03) {
                return number_format(
                    $originalFirstLineAMount - $diff,
                    2,
                    '.',
                    ''
                );
            }
        }

        return $originalFirstLineAMount;
    }
}