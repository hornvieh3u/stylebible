<?php

namespace RebelCode\Spotlight\Instagram\Modules;

use Dhii\Services\Factories\GlobalVar;
use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use Psr\Container\ContainerInterface;
use RebelCode\Spotlight\Instagram\Module;
use RebelCode\Spotlight\Instagram\Utils\Arrays;
use RebelCode\Spotlight\Instagram\Wp\CronJob;
use RebelCode\Spotlight\Instagram\Wp\Menu;
use RebelCode\Spotlight\Instagram\Wp\NoticesManager;
use RebelCode\Spotlight\Instagram\Wp\PostType;
use RebelCode\Spotlight\Instagram\Wp\Shortcode;

/**
 * A module that contains services for various WordPress objects.
 *
 * @since 0.1
 */
class WordPressModule extends Module
{
    /**
     * @inheritDoc
     *
     * @since 0.1
     */
    public function getFactories() : array
    {
        return [
            'db' => new GlobalVar('wpdb'),
            'post_types' => new Value([]),
            'cron_jobs' => new Value([]),
            'shortcodes' => new Value([]),
            'widgets' => new Value([]),
            'menus' => new Value([]),
            'block_types' => new Value([]),
            // Notices
            'notices' => new Value([]),
            'notices/dismiss_nonce' => new Value('sli-dismiss-nonce'),
            'notices/dismiss_action' => new Value('sli-dismiss-notice'),
            'notices/manager' => new Factory(
                ['@ui/static_url', 'notices/dismiss_nonce', 'notices/dismiss_action', 'notices'],
                function (string $staticUrl, string $nonce, string $action, array $notices) {
                    return new NoticesManager($staticUrl . '/notices.js', $nonce, $action, $notices);
                }
            ),
        ];
    }

    /**
     * @inheritDoc
     *
     * @since 0.1
     */
    public function run(ContainerInterface $c): void
    {
        // Register the cron jobs.
        // This hooks in the cron job handlers and takes care of scheduling and updating the cron job events.
        Arrays::each($c->get('cron_jobs'), [CronJob::class, 'register']);

        add_action('init', function () use ($c) {
            // Register the CPTs
            Arrays::each($c->get('post_types'), [PostType::class, 'register']);

            // Register the shortcodes
            Arrays::each($c->get('shortcodes'), [Shortcode::class, 'register']);

            // Register the block types
            Arrays::each($c->get('block_types'), 'register_block_type');
        });

        // Registers the menus for the WP Admin sidebar
        add_action('admin_menu', function () use ($c) {
            Arrays::each($c->get('menus'), [Menu::class, 'register']);
        });

        // Registers the widget
        add_action('widgets_init', function () use ($c) {
            Arrays::each($c->get('widgets'), 'register_widget');
        });

        // Register the notice dismissal AJAX handler
        $action = $c->get('notices/dismiss_action');
        add_action("wp_ajax_{$action}", function () use ($c) {
            /* @var NoticesManager $nm */
            $nm = $c->get('notices/manager');
            $nm->handleAjax();
            die;
        });
    }
}
