<?php

namespace App\Security;

use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubUserProvider implements UserProviderInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private SerializerInterface $serializer
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $response = $this->client->request('GET', 'https://api.github.com/user', [
            'headers' => [
                'Authorization' => 'token ' . $identifier,
                'Accept'        => 'application/vnd.github+json',
            ]
        ]);

        $userData = $this->serializer->deserialize(
            $response->getContent(),
            'array',
            'json'
        );

        if (!$userData) {
            throw new \LogicException('Did not managed to get your user info from Github.');
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
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException();
        }
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}