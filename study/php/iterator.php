<?php

/**
 * Class myIterator迭代器
 */
class myIterator implements Iterator {
    private $position = 0;
    private $array = array(
        "firstelement",
        "secondelement",
        "lastelement",
    );

    public function __construct() {
        $this->position = 0;
    }

    public function rewind() {
        var_dump(__METHOD__);
        $this->position = 0;
    }

    public function current() {
        var_dump(__METHOD__);
        return $this->array[$this->position];
    }

    public function key() {
        var_dump(__METHOD__);
        return $this->position;
    }

    public function next() {
        var_dump(__METHOD__);
        ++$this->position;
    }

    public function valid() {
        var_dump(__METHOD__);
        return isset($this->array[$this->position]);
    }
}

//foreach循环
$it = new myIterator;
foreach($it as $key => $value) {
    //var_dump($key, $value);
    echo "\n";
}


//while循环
$it->rewind();
while ($it->valid())
{
    $key = $it->key();
    $value = $it->current();
    $it->next();
}

//for循环
for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
    try {
        $value = $iterator->current();
    } catch (Exception $exception) {
        continue;
    }
}
?>