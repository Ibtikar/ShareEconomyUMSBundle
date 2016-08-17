<?php

namespace Ibtikar\ShareEconomyUMSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ibtikar_share_economy_ums');

        $rootNode
            ->children()
                ->scalarNode('frontend_layout')
                    ->defaultValue(null)
                ->end()
                ->scalarNode('max_daily_forget_passwords_requests')
                    ->defaultValue(5)
                ->end()
                ->scalarNode('max_daily_verification_code_requests')
                    ->defaultValue(5)
                ->end()
                ->scalarNode('max_daily_verification_email_requests')
                    ->defaultValue(5)
                ->end()
                ->scalarNode('verification_code_expiry_minutes')
                    ->defaultValue(5)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
