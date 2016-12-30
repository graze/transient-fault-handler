<?php

namespace Graze\TransientFaultHandler\Test;

use InvalidArgumentException;

class CartesianProduct
{
    /**
     * Returns the cartesian product of the sets given in $sets.
     * Example: cartesianProduct([['Blue', 'Red'], [1, 2]]) = [['Blue', 1], ['Blue', 2], ['Red', 1], ['Red', 2]]
     *
     * @param array $sets {X_1, ..., X_n}
     * @return array {(x_1, ..., x_n) : x_i \in X_i}
     */
    public function build(array $sets)
    {
        // Base case: empty array
        if (!$sets) {
            return [[]];
        }

        // Pop the first set from the array of set
        $set = array_shift($sets);

        // Make sure $set is actually a set
        if (!is_array($set)) {
            throw new InvalidArgumentException("Input must be an array of arrays");
        }

        // Recursively build the cartesian product of the remaining sets
        $cartesianProduct = $this->build($sets);

        $result = [];
        // For each element in the popped set
        foreach ($set as $element) {
            // Foreach tuple in the recursively built cartesian product
            foreach ($cartesianProduct as $tuple) {
                // Insert the element into the tuple
                array_unshift($tuple, $element);
                // Append the resulting tuple to $result
                $result[] = $tuple;
            }
        }

        return $result;
    }
}
