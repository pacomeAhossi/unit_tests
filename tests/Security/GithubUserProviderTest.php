<?php

use App\Entity\User;
use App\Security\GithubUserProvider;
use JMS\Serializer\Serializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GithubUserProviderTest extends TestCase{

  private MockObject | HttpClientInterface | null $client;
  private MockObject | Serializer | null $serializer;
  private MockObject | ResponseInterface | null $response;

  public function setUp(): void
  {
    $this->client = $this->getMockBuilder('Symfony\Contracts\HttpClient\HttpClientInterface')
                    ->disableOriginalConstructor()
                    ->getMock()
    ; 

    $this->serializer = $this->getMockBuilder('JMS\Serializer\SerializerInterface')
                       ->disableOriginalConstructor()
                       ->getMock()
    ;

    // L'objet à doubler qui servira de stub à l'objet $client :Stub ResponseInterface
    $this->response = $this->getMockBuilder(ResponseInterface::class)
                    ->disableOriginalConstructor()->getMock()
    ;
  }

  public function testLoadUserByIdentifierReturningAUser() {

    // Stub de HttpClientInterface
    $this->client->expects($this->once()) // On s'attend à ce que la méthode request soit appelée une seule fois
          ->method('request')->willReturn($this->response)
    ;

    // getContent() retournera un objet fictif
    $this->response->expects($this->once())
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
    $this->serializer
        ->expects($this->once())
        ->method('deserialize')
        ->willReturn($userData)
    ;

    $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

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
    
    // Stub de HttpClientInterface
    $this->client->expects($this->once()) // On s'attend à ce que la méthode request soit appelée une seule fois
          ->method('request')->willReturn($this->response)
    ;

    // getContent() retournera un objet fictif
    $this->response->expects($this->once())
          ->method('getContent')
          ->willReturn('{"login":"pahmas","name":"Pacome AHOSSI","email":"pahmas@gmail.com","avatar_url":"http://avatar.url","html_url":"http://github.com/pahmas"}')
    ;

    // Stub de JMS Serializer
    $this->serializer
        ->expects($this->once())
        ->method('deserialize')
        ->willReturn([])
    ;

    $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

    // On spécifie l'exception attendue avant d'exécuter la méthode
    $this->expectException(\LogicException::class);

    // appel de la méthode loadUserByIdentifier sur l'objet $githubUserProvider
    $user = $githubUserProvider->loadUserByIdentifier('an-token-acess');
  }

  // Pour libérer la mémoire à la fin de chaque test
  public function tearDown(): void
  {
    $this->client = null;
    $this->serializer = null;
    $this->response = null;
  }
}