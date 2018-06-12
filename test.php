<?php

require_once 'utils.php';

# 中介者模式


abstract class AbstractMediator
{
    protected $purchase;
    protected $sale;
    protected $stock;

    public function __construct($purchase, $sale, $stock)
    {
        $this->purchase = $purchase;
        $this->sale = $sale;
        $this->stock = $stock;
    }

    abstract public function execute();
}

class Mediator extends AbstractMediator
{
    public function execute()
    {
        // TODO: Implement execute() method.
    }
}


abstract class AbstractColleague
{
    protected $mediator;

    public function __construct(AbstractMediator $mediator)
    {
        $this->mediator = $mediator;
    }
}


class Purchase extends AbstractColleague
{
    public function buyIBMComputer()
    {

    }

    public function refuseBuyIBM($number)
    {
        $this->mediator->execute();
    }
}