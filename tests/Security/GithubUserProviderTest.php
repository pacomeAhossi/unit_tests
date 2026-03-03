<?php

use App\Entity\User;
use App\Security\GithubUserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GithubUserProviderTest extends TestCase{

  public function testLoadUserByIdentifierReturningAUser() {

    $client = $this->getMockBuilder('Symfony\Contracts\HttpClient\HttpClientInterface')
                    ->disableOriginalConstructor()
                    ->getMock()
    ;  
  
    
    $serializer = $this->getMockBuilder('JMS\Serializer\SerializerInterface')
                       ->disableOriginalConstructor()
                       ->getMock()
    ;

    // L'objet à doubler qui servira de stub à l'objet $client :Stub ResponseInterface
    $response = $this->getMockBuilder(ResponseInterface::class)
                    ->disableOriginalConstructor()->getMock()
    ;

    
    // Stub de HttpClientInterface
    $client->expects($this->once()) // On s'attend à ce que la méthode request soit appelée une seule fois
           ->method('request')->willReturn($response);

    // getContent() retournera un objet fictif
    $response->expects($this->once())
      ->method('getContent')
      ->willReturn('{"login":"pahmas","name":"Pacome AHOSSI","email":"pahmas@gmail.com","avatar_url":"http://avatar.url","html_url":"http://github.com/pahmas"}')
    ;

    // exemple de data d'un user qui sera retournée
    $userData = [
      'login' => 'pahmas', 
      'name' => 'Pacome AHOSSI', 
      'email' => 'pahmas@gmail.com', 
      'avatar_url' => 'http://avatar.url',
      'html_url' => 'http://github.com/pahmas',
    ];

    // Stub de JMS Serializer
    $serializer
        ->expects($this->once())
        ->method('deserialize')->willReturn($userData)
    ;

    $githubUserProvider = new GithubUserProvider($client, $serializer);

    // appel de la méthode loadUserByIdentifier sur l'objet $githubUserProvider
    $user = $githubUserProvider->loadUserByIdentifier('an-token-acess');

    assert($user instanceof User);
    
    // User expected
    $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);
    
    // Assertions
    $this->assertEquals($expectedUser, $user);
    
    $this->assertInstanceOf(User::class, $user);
  }

  public function testLoadUserByIdentifierThrowingException() {

    $client = $this->getMockBuilder('Symfony\Contracts\HttpClient\HttpClientInterface')
                    ->disableOriginalConstructor()
                    ->getMock()
    ;  
  
    
    $serializer = $this->getMockBuilder('JMS\Serializer\SerializerInterface')
                       ->disableOriginalConstructor()
                       ->getMock()
    ;

    // L'objet à doubler qui servira de stub à l'objet $client :Stub ResponseInterface
    $response = $this->getMockBuilder(ResponseInterface::class)
                    ->disableOriginalConstructor()->getMock()
    ;

    
    // Stub de HttpClientInterface
    $client->expects($this->once()) // On s'attend à ce que la méthode request soit appelée une seule fois
           ->method('request')->willReturn($response);

    // getContent() retournera un objet fictif
    $response->expects($this->once())
      ->method('getContent')
      ->willReturn('{"login":"pahmas","name":"Pacome AHOSSI","email":"pahmas@gmail.com","avatar_url":"http://avatar.url","html_url":"http://github.com/pahmas"}')
    ;

    // exemple de data d'un user qui sera retournée
    $userData = [
      'login' => 'pahmas', 
      'name' => 'Pacome AHOSSI', 
      'email' => 'pahmas@gmail.com', 
      'avatar_url' => 'http://avatar.url',
      'html_url' => 'http://github.com/pahmas',
    ];

    // Stub de JMS Serializer
    $serializer
        ->expects($this->once())
        ->method('deserialize')->willReturn([])
    ;

    $githubUserProvider = new GithubUserProvider($client, $serializer);

    // On spécifie l'exception attendue avant d'exécuter la méthode
    $this->expectException(\LogicException::class);

    // appel de la méthode loadUserByIdentifier sur l'objet $githubUserProvider
    $user = $githubUserProvider->loadUserByIdentifier('an-token-acess');
  }
}