<?php
function rectSort($arrays, $field, $sort_type = SORT_ASC, $sort_mode = SORT_REGULAR)
{
    if (is_array($arrays)) {
        foreach ($arrays as $array) {
            if (is_array($array)) {
                $key_arrays[] = $array[$field];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_arrays, $sort_type, $sort_mode, $arrays);
    return $arrays;
}

$person = array(
    array('id' => 1, 'name' => 'fj', 'weight' => 100, 'height' => 180),
    array('id' => 2, 'name' => 'tom', 'weight' => 53, 'height' => 150),
    array('id' => 3, 'name' => 'jerry', 'weight' => 120, 'height' => 156),
    array('id' => 4, 'name' => 'bill', 'weight' => 110, 'height' => 190),
    array('id' => 5, 'name' => 'linken', 'weight' => 80, 'height' => 200),
    array('id' => 6, 'name' => 'madana', 'weight' => 95, 'height' => 110),
    array('id' => 7, 'name' => 'jordan', 'weight' => 70, 'height' => 170)
);

echo "<pre>";

$person = rectSort($person, 'name');

print_r($person);

$person = rectSort($person, 'height');

print_r($person);