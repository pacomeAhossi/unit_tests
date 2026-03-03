<?php

use App\Entity\User;
use App\Security\GithubUserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GithubUserProviderTest extends TestCase{

  public function testLoadUserByIdentifierReturningAUser() {

    // L'objet à doubler qui servira de stub à l'objet $client :Stub ResponseInterface
    $response = $this->getMockBuilder(ResponseInterface::class)->disableOriginalConstructor()->getMock();
    
    // getContent() retournera un objet fictif
  
  
    // Stub de HttpClientInterface
    $client = $this->getMockBuilder('Symfony\Contracts\HttpClient\HttpClientInterface')
                    ->disableOriginalConstructor()
                    ->getMock();

    $client
          ->expects($this->once()) // On s'attend à ce que la méthode request soit appelée une seule fois
          ->method('request')->willReturn($response);

    $response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"login":"pahmas","name":"Pacome AHOSSI","email":"pahmas@gmail.com","avatar_url":"http://avatar.url","html_url":"http://github.com/pahmas"}');

    // Stub de JMS Serializer
    $serializer = $this->getMockBuilder('JMS\Serializer\SerializerInterface')
                          ->disableOriginalConstructor()
                          ->getMock();
    $userData = [
      'login' => 'pahmas', 
      'name' => 'Pacome AHOSSI', 
      'email' => 'pahmas@gmail.com', 
      'avatar_url' => 'http://avatar.url',
      'html_url' => 'http://github.com/pahmas',
    ];

    $serializer
        ->expects($this->once())
        ->method('deserialize')->willReturn($userData);

    $githubUserProvider = new GithubUserProvider($client, $serializer);

    $user = $githubUserProvider->loadUserByIdentifier('an-token-acess');

    assert($user instanceof User);
    
    // User expected
    $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);
    // Assertions
    $this->assertEquals($expectedUser, $user);
    
    $this->assertInstanceOf(User::class, $user);
  }
}