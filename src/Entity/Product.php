<?php
namespace App\Entity;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\Exception\Exception;

class Product{
    const FOOD_PRODUCT = 'food';
  private string $name;
  private string $type;
  private float $price;
    
    public function __construct($name, $type, $price)
    {
        $this->name = $name;
        $this->type = $type;
        $this->price = $price;
    }
    public function computeTVA(): float | Exception
    {
        if ($this->price < 0){
            throw new InvalidArgumentException('The TVA cannot be negative');
        }
        if (self::FOOD_PRODUCT == $this->type) {
            return $this->price * 0.055;
        }

        return $this->price*0.196;
    }
}