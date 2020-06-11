<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Bundle\SecurityBundle\Security\UserAuthenticator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;
use Symfony\Component\Security\Http\Authentication\NoopAuthenticationManager;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\HttpBasicAuthenticator;
use Symfony\Component\Security\Http\Authenticator\JsonLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\RememberMeAuthenticator;
use Symfony\Component\Security\Http\Authenticator\RemoteUserAuthenticator;
use Symfony\Component\Security\Http\Authenticator\X509Authenticator;
use Symfony\Component\Security\Http\EventListener\CheckCredentialsListener;
use Symfony\Component\Security\Http\EventListener\RememberMeListener;
use Symfony\Component\Security\Http\EventListener\SessionStrategyListener;
use Symfony\Component\Security\Http\EventListener\UserCheckerListener;
use Symfony\Component\Security\Http\Firewall\AuthenticatorManagerListener;

return static function (ContainerConfigurator $container) {
    $container->services()

        // Manager
        ->set('security.authenticator.manager', AuthenticatorManager::class)
            ->abstract()
            ->tag('monolog.logger', ['channel' => 'security'])
            ->args([
                [], // authenticators
                service('security.token_storage'),
                service('event_dispatcher'),
                null, // provider key
                service('logger')->nullOnInvalid(),
                param('security.authentication.manager.erase_credentials'),
            ])

        ->set('security.authenticator.managers_locator', ServiceLocator::class)
            ->args([[]])

        ->set('security.user_authenticator', UserAuthenticator::class)
            ->args([
                service('security.firewall.map'),
                service('security.authenticator.managers_locator'),
                service('request_stack'),
            ])
        ->alias(UserAuthenticatorInterface::class, 'security.user_authenticator')

        ->set('security.authentication.manager', NoopAuthenticationManager::class)
        ->alias(AuthenticationManagerInterface::class, 'security.authentication.manager')

        ->set('security.firewall.authenticator', AuthenticatorManagerListener::class)
            ->abstract()
            ->args([
                null, // authenticator manager
            ])

        // Listeners
        ->set('security.listener.check_authenticator_credentials', CheckCredentialsListener::class)
            ->tag('kernel.event_subscriber')
            ->args([
               service('security.encoder_factory'),
            ])

        ->set('security.listener.user_checker', UserCheckerListener::class)
            ->abstract()
            ->args([
                abstract_arg('user checker'),
            ])

        ->set('security.listener.session', SessionStrategyListener::class)
            ->abstract()
            ->args([
                service('security.authentication.session_strategy'),
            ])

        ->set('security.listener.remember_me', RememberMeListener::class)
            ->abstract()
            ->tag('monolog.logger', ['channel' => 'security'])
            ->args([
                [], // remember me services
                service('logger')->nullOnInvalid(),
            ])

        // Authenticators
        ->set('security.authenticator.http_basic', HttpBasicAuthenticator::class)
            ->abstract()
            ->tag('monolog.logger', ['channel' => 'security'])
            ->args([
                null, // realm name
                null, // user provider
                service('logger')->nullOnInvalid(),
            ])

        ->set('security.authenticator.form_login', FormLoginAuthenticator::class)
            ->abstract()
            ->args([
                service('security.http_utils'),
                null, // user provider
                null, // authentication success handler
                null, // authentication failure handler
                [], // options
            ])

        ->set('security.authenticator.json_login', JsonLoginAuthenticator::class)
            ->abstract()
            ->args([
                service('security.http_utils'),
                null, // user provider
                null, // authentication success handler
                null, // authentication failure handler
                [], // options
                service('property_accessor')->nullOnInvalid(),
            ])

        ->set('security.authenticator.remember_me', RememberMeAuthenticator::class)
            ->abstract()
            ->args([
                [], // remember me services
                param('kernel.secret'),
                service('security.token_storage'),
                [], // options
                service('security.authentication.session_strategy'),
            ])

        ->set('security.authenticator.x509', X509Authenticator::class)
            ->abstract()
            ->tag('monolog.logger', ['channel' => 'security'])
            ->args([
                null, // user provider
                service('security.token_storage'),
                null, // firewall name
                null, // user key
                null, // credentials key
                service('logger')->nullOnInvalid(),
            ])

        ->set('security.authenticator.remote_user', RemoteUserAuthenticator::class)
            ->abstract()
            ->tag('monolog.logger', ['channel' => 'security'])
            ->args([
                null, // user provider
                service('security.token_storage'),
                null, // firewall name
                null, // user key
                service('logger')->nullOnInvalid(),
            ])
    ;
};
