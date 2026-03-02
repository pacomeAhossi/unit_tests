<?php

namespace App\Tests\Entity;
use App\Entity\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase {
  /**
   *
   * @param [type] $price
   * @param [type] $expectedTVA
   * @return void
   * 
   */
  #[DataProvider('pricesForFoodProduct')]
  public function testComputeTVAFoodProduct($price, $expectedTVA){
    $product = new Product("Pomme", "food", $price);
    $this->assertSame($expectedTVA, $product->computeTVA());

  }

  public function testComputeTVAOtherProduct(){
    $product = new Product('computer', 'technology', 1);
    // $this->assertSame("Incompatible type", $product->computeTVA() );
    $this->assertSame(0.196, $product->computeTVA());
  }

  public function testNegativePrice(){
    $product = new Product("Un produit", Product::FOOD_PRODUCT, -20);
    // On spécifie l'exception attendue; 
    // $this->expectException('Exception');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("The TVA cannot be negative");
    // On exécute la méthode
    $product->computeTVA();
  }

  public static function pricesForFoodProduct(){
    return [
      [0, 0.0],
      [20, 1.1],
      [100, 5.5],
    ];
  }

}