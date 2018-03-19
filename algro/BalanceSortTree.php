<?php

class BalanceSortTree
{
    public $left;
    public $right;
    public $parent;
    public $height;
    public $value;

    public function insert($value)
    {
        if (!$this->value) {
            $this->parent = null;
            $this->value = $value;
            $this->left = null;
            $this->right = null;
            $this->height = 0;
            return true;
        }

        if ($this->value == $value) {
            return false;
        }

        if ($value > $this->value) {
            if ($this->right) {
                $this->right->insert($value);
            } else {
                $tree = new BalanceSortTree();
                $tree->parent = $this;
                $tree->value = $value;
                $tree->left = null;
                $tree->right = null;
                $tree->height = 0;

                //更新父节点高度
                $t = $tree;
                while ($t->parent) {
                    if ($t->parent->height < $t->height + 1) {
                        $t = $t->parent;
                        $t->height++;
                    } else {
                        break;
                    }
                }

                $this->right = $tree;

                if ($this->parent) {
                    $this->parent->keepBalance();
                }
            }
            return true;
        }

        if ($value < $this->value) {
            if ($this->left) {
                $this->left->insert($value);
            } else {
                $tree = new BalanceSortTree();
                $tree->parent = $this;
                $tree->value = $value;
                $tree->height = 0;
                $tree->left = null;
                $tree->right = null;

                $t = $tree;
                while ($t->parent) {
                    if ($t->parent->height < $t->height + 1) {
                        $t = $t->parent;
                        $t->height++;
                    } else {
                        break;
                    }
                }

                $this->left = $tree;

                if ($this->parent) {
                    $this->parent->keepBalance();
                }
            }

            return true;
        }
    }

    public function keepBalance()
    {
        $this->_keepBalance();
        if ($this->parent) {
            $this->parent->_keepBalance();
        }
    }

    private function _keepBalance()
    {
        $left_height = self::height($this->left);
        $right_height = self::height($this->right);

        if (abs($left_height - $right_height) > 1) {
            if ($left_height > $right_height) {
                if (self::height($this->left->left) > self::height($this->left->right)) {
                    $this->rightRolate();
                } else {
                    $this->left->leftRolate();
                    $this->rightRolate();
                }
            } else {
                if (self::height($this->right->left) > self::height($this->right->right)) {
                    $this->right->rightRolate();
                    $this->leftRolate();
                } else {
                    $this->leftRolate();
                }
            }
        }
    }


    public function search($value)
    {
        if ($this->value == $value) {
            return true;
        }

        if ($this->value > $value) {
            if ($this->left) {
                return $this->left->search($value);
            } else {
                return false;
            }
        }

        if ($this->value < $value) {
            if ($this->right) {
                return $this->right->search($value);
            } else {
                return false;
            }
        }
    }

    public function delete($value)
    {
        if ($this->value == $value) {
            //待删除节点没有左右子节点
            if (!$this->left && !$this->right) {
                if ($this->parent) {
                    if ($this->value > $this->parent->value) {
                        $this->parent->right = null;
                    }
                    if ($this->value < $this->parent->value) {
                        $this->parent->left = null;
                    }
                } else {
                    $this->value = null;
                }
                $this->destroy();
                return true;
            }

            //待删除节点只有左节点
            if ($this->left && !$this->right) {
                if ($this->parent) {
                    if ($this->left->value > $this->parent->value) {
                        $this->parent->right = $this->left;
                    }
                    if ($this->left->value < $this->parent->value) {
                        $this->parent->left = $this->left;
                    }

                    $t = $this->parent;
                    while ($t) {
                        $t->height = max(self::height($t->left), self::height($t->right)) + 1;
                        $t = $t->parent;
                    }

                    $this->destroy();
                } else {
                    $t = $this->left;
                    $this->value = $t->value;

                    if ($t->left) {
                        $t->left->parent = $this;
                    }

                    if ($t->right) {
                        $t->right->parent = $this;
                    }

                    $this->left = $t->left;
                    $this->right = $t->right;
                    $this->height = max(self::height($this->left), self::height($this->right)) + 1;
                    $t->destroy();
                }
                return true;
            }

            //待删除节点只有右节点
            if ($this->right && !$this->left) {
                if ($this->parent) {
                    if ($this->right->value > $this->parent->value) {
                        $this->parent->right = $this->right;
                    }
                    if ($this->right->value < $this->parent->value) {
                        $this->parent->left = $this->right;
                    }

                    $t = $this->parent;
                    while ($t) {
                        $t->height = max(self::height($t->left), self::height($t->right)) + 1;
                        $t = $t->parent;
                    }

                    $this->destroy();
                } else {
                    $t = $this->right;
                    $this->value = $t->value;
                    if ($t->left) {
                        $t->left->parent = $this;
                    }

                    if ($t->right) {
                        $t->right->parent = $this;
                    }
                    $this->left = $t->left;
                    $this->right = $t->right;

                    $this->height = max(self::height($this->left), self::height($this->right)) + 1;

                    $t->destroy();
                }
                return true;
            }

            //待删除节点左右节点都有
            if ($this->left && $this->right) {
                $t = $this->right;
                //待删除节点的右节点没有左节点时
                if (!$t->left) {
                    $this->value = $t->value;
                    $this->right = $t->right;
                } else {
                    while ($t->left) {
                        $t = $t->left;
                    }
                    $this->value = $t->value;
                    $t->parent->right = $t->right;
                }
                $t->destroy();
                return true;
            }
        }

        if ($this->value > $value && $this->left) {
            return $this->left->delete($value);
        }

        if ($this->value < $value && $this->right) {
            return $this->right->delete($value);
        }
    }

    public static function height($tree)
    {
        if ($tree) {
            return $tree->height;
        } else {
            return -1;
        }
    }

    //左旋
    public function leftRolate()
    {
        $x = $this;
        $y = $this->right;

        //交换值
        $x->value ^= $y->value;
        $y->value ^= $x->value;
        $x->value ^= $y->value;

        $x->right = $y->right;
        if ($y->right) {
            $y->right->parent = $x;
        }

        $y->right = $y->left;

        $y->left = $x->left;
        if ($x->left) {
            $x->left->parent = $y;
        }

        $x->left = $y;

        $y->height = max(self::height($y->left), self::height($y->right)) + 1;
        $x->height = max(self::height($x->left), self::height($x->right)) + 1;


        //高度修改
        $t = $x;
        while ($t->parent) {
            $t = $t->parent;
            $t->height = max(self::height($t->left), self::height($t->right)) + 1;
        }
    }

    //右旋
    public function rightRolate()
    {
        $x = $this;
        $y = $this->left;

        //交换值
        $x->value ^= $y->value;
        $y->value ^= $x->value;
        $x->value ^= $y->value;

        $x->left = $y->left;
        if ($y->left) {
            $y->left->parent = $x;
        }

        $y->left = $y->right;

        $y->right = $x->right;
        if ($x->right) {
            $x->right->parent = $y;
        }

        $x->right = $y;

        $y->height = max(self::height($y->left), self::height($y->right)) + 1;
        $x->height = max(self::height($x->left), self::height($x->right)) + 1;
        //高度修改
        $t = $x;
        while ($t->parent) {
            $t = $t->parent;
            $t->height = max(self::height($t->left), self::height($t->right)) + 1;
        }
    }

    public function destroy()
    {
        $this->parent = null;
        $this->left = null;
        $this->right = null;
        $this->value = null;
    }

    //广度优先遍历
    public static function levelDisplay(BalanceSortTree $tree)
    {
        $queue = new SplQueue();
        $queue->enqueue($tree);
        while (!$queue->isEmpty()) {
            $node = $queue->dequeue();
            echo $node->value . "-" . $node->height . "<br/>";
            if ($node->left) {
                $queue->enqueue($node->left);
            }
            if ($node->right) {
                $queue->enqueue($node->right);
            }
        }
    }
}