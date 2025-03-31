<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use TheNetworg\OAuth2\Client\Provider\AzureResourceOwner;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    use TargetPathTrait;

    public function __construct(private ClientRegistry $clientRegistry, private EntityManagerInterface $entityManager, private RouterInterface $router, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function supports(Request $request): ?bool
    {
        return in_array($request->attributes->get('_route'), [
            'connect_google_check',
        ]);
    }

    public function authenticate(Request $request): Passport
    {
        [$provider, $client] = match ($request->attributes->get('_route')) {
            'connect_google_check' => ['google', $this->clientRegistry->getClient('google')],
        };

        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client, $provider) {
                /** @var GoogleUser|GithubResourceOwner $oauthUser */
                $oauthUser = $client->fetchUserFromToken($accessToken);

                $email = match($provider) {
                    'google' => $oauthUser->getEmail(),
                    'github' => $oauthUser->getEmail(),
                };

                $existingUser = $this->entityManager->getRepository(User::class)->findOneByCriteria(['externalId' => $provider.':'.$oauthUser->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

                /** @var User $user */
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email])
                    ?: $this->entityManager->getRepository(User::class)->findOneBy(['lastname' => $oauthUser->getLastName(), 'firstname' => $oauthUser->getFirstName()])
                    ?: new User();

                $user->addExternalId($provider, $oauthUser->getId());

                if (!$user->getEmail() || preg_match('/temp-.*@eemi\.com/', $user->getEmail()) !== false) {
                    $user->setEmail($email);
                }

                if (!$user->getFirstname()) {
                    $user->setFirstName($oauthUser->getFirstName());
                }
                if (!$user->getLastName()) {
                    $user->setLastName($oauthUser->getLastName());
                }
                if (!$user->getRoles()) {
                    $user->setRoles(['ROLE_USER']);
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            }),
            [
                new RememberMeBadge()
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        if ($targetPath and $targetPath !== $this->urlGenerator->generate('app_welcome', [], UrlGeneratorInterface::ABSOLUTE_URL)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_welcome'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/login',
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}