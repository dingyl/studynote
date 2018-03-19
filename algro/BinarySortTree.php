<?php
//二叉排序树
class BinarySortTree{
    public $left;
    public $right;
    public $parent;
    public $value;

    public function insert($value){
        if(!$this->value){
            $this->value = $value;
            $this->parent = null;
            $this->left = null;
            $this->right = null;
            return true;
        }

        if($this->value == $value){
            return false;
        }

        if($value > $this->value){
            if($this->right){
                return $this->right->insert($value);
            }else{
                $tree = new BinarySortTree();
                $tree->parent = $this;
                $tree->value = $value;
                $tree->left = null;
                $tree->right = null;
                $this->right = $tree;
                return true;
            }
        }

        if($value < $this->value){
            if($this->left){
                return $this->left->insert($value);
            }else{
                $tree = new BinarySortTree();
                $tree->parent = $this;
                $tree->value = $value;
                $tree->left = null;
                $tree->right = null;
                $this->left = $tree;
                return true;
            }
        }
    }

    public function search($value){
        if($this->value == $value){
            return true;
        }

        if($this->value > $value){
            if($this->left){
                return $this->left->search($value);
            }else{
                return false;
            }
        }

        if($this->value < $value){
            if($this->right){
                return $this->right->search($value);
            }else{
                return false;
            }
        }
    }

    public function delete($value){
        if($this->value == $value){
            //待删除节点没有左右子节点
            if(!$this->left && !$this->right){
                if($this->parent){
                    if($this->value > $this->parent->value){
                        $this->parent->right = null;
                    }
                    if($this->value < $this->parent->value){
                        $this->parent->left = null;
                    }
                }else{
                    $this->value = null;
                }
                $this->destroy();
                return true;
            }

            //待删除节点只有左节点
            if($this->left && !$this->right){
                if($this->parent){
                    if($this->left->value > $this->parent->value){
                        $this->parent->right = $this->left;
                    }
                    if($this->left->value < $this->parent->value){
                        $this->parent->left = $this->left;
                    }
                    $this->destroy();
                }else{
                    $t = $this->left;
                    $this->value = $t->value;

                    if($t->left){
                        $t->left->parent = $this;
                    }

                    if($t->right){
                        $t->right->parent = $this;
                    }

                    $this->left = $t->left;
                    $this->right = $t->right;
                    $t->destroy();
                }
                return true;
            }

            //待删除节点只有右节点
            if($this->right && !$this->left){
                if($this->parent){
                    if($this->right->value > $this->parent->value){
                        $this->parent->right = $this->right;
                    }
                    if($this->right->value < $this->parent->value){
                        $this->parent->left = $this->right;
                    }
                    $this->destroy();
                }else{
                    $t = $this->right;
                    $this->value = $t->value;
                    if($t->left){
                        $t->left->parent = $this;
                    }

                    if($t->right){
                        $t->right->parent = $this;
                    }
                    $this->left = $t->left;
                    $this->right = $t->right;
                    $t->destroy();
                }
                return true;
            }

            //待删除节点左右节点都有
            if($this->left && $this->right){
                $t = $this->right;
                //待删除节点的右节点没有左节点时
                if(!$t->left){
                    $this->value = $t->value;
                    $this->right = $t->right;
                }else{
                    while($t->left){
                        $t = $t->left;
                    }
                    $this->value = $t->value;
                    $t->parent->right = $t->right;
                }
                $t->destroy();
                return true;
            }
        }

        if($this->value > $value && $this->left){
            return $this->left->delete($value);
        }

        if($this->value < $value && $this->right){
            return $this->right->delete($value);
        }
    }

    public function destroy(){
        $this->parent = null;
        $this->left = null;
        $this->right = null;
        $this->value = null;
    }

    //广度优先遍历
    public static function levelDisplay(BinarySortTree $tree){
        $queue = new SplQueue();
        $queue->enqueue($tree);
        while (!$queue->isEmpty()){
            $node = $queue->dequeue();
            echo $node->value."<br/>";
            if($node->left){
                $queue->enqueue($node->left);
            }
            if($node->right){
                $queue->enqueue($node->right);
            }
        }
    }
}