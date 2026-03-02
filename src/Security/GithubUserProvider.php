<?php

namespace App\Security;

use App\Entity\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Config\Exception\LogicException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubUserProvider implements UserProviderInterface
{
    private HttpClientInterface $client;
    private SerializerInterface $serializer;

    public function __construct(HttpClientInterface $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * @throws GuzzleException
     */
    public function loadUserByUsername(string $username): User
    {
        $response = $this->client->request('get', 'https://api.github.com/user?access_token='.$username);
        // $response = $this->client->get('https://api.github.com/user?access_token='.$username);
        // $result = $response->getBody()->getContents();
        $result = $response->getContent();

        $userData = $this->serializer->deserialize($result, 'array', 'json');

        if (!$userData) {
            throw new LogicException('Did not managed to get your user info from Github.');
        }

        return new User(
            $userData['login'],
            $userData['name'],
            $userData['email'],
            $userData['avatar_url'],
            $userData['html_url']
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException();
        }
        return $user;
    }

    public function supportsClass($class): bool
    {
        return 'App\Entity\User' === $class;
    }


    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new User('dDupont', 'dupont', 'ddupont@test.fr','avatarUrl','profilHtmlUrl');
    }
}