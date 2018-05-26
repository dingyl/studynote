<?php

# 定义

class ColorIterator implements Iterator
{

    private $array = [];
    private $valid = false;

    function __construct($array)
    {
        $this->array = $array;
    }


    function rewind()
    {
        $this->valid = (false !== reset($this->array));
    }

    function current()
    {
        return current($this->array);
    }

    function next()
    {
        $this->valid = (false !== next($this->array));
    }

    function key()
    {
        return key($this->array);
    }


    function valid()
    {
        return $this->valid;
    }
}

# 使用

$array = ['red', 'blue', 'green'];

$colors = new ColorIterator($array);

# 方式一
foreach ($colors as $color) {
    echo $color;
}


# 方式二

$colors->rewind();
while ($colors->valid()) {
    echo $colors->key() . ": " . $colors->current() . "";
    $colors->next();
}

