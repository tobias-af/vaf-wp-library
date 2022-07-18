<?php

/**
 * @noinspection PhpUnused
 * @noinspection PhpUnusedParameterInspection
 */

namespace VAF\WP\Library\Features;

use InvalidArgumentException;
use VAF\WP\Library\RestRoute;
use WP_REST_Request;

final class RestAPI extends AbstractFeature
{
    protected function getParameters(): array
    {
        return [
            'restNamespace' => [
                'required' => true
            ],
            'restRoutes' => [
                'required' => true
            ]
        ];
    }

    final public function getRestNamespace(): string
    {
        return $this->getParameter('restNamespace');
    }

    final public function start(): self
    {
        $restRoutes = $this->getParameter('restRoutes');

        add_filter('rest_api_init', function () use ($restRoutes) {
            foreach ($restRoutes as $route) {
                $this->registerRestRoute($route);
            }
        });

        return $this;
    }

    final private function registerRestRoute(string $classname): void
    {
        if (!is_subclass_of($classname, 'VAF\WP\Library\RestRoute')) {
            throw new InvalidArgumentException('Module must inherit VAF\WP\Library\RestRoute!');
        }

        /** @var RestRoute $route */
        $route = new $classname();
        $route->setPlugin($this->getPlugin());

        register_rest_route(
            $this->getRestNamespace(),
            $route->getRoute(),
            [
                'methods' => $route->getMethod(),
                'callback' => function (WP_REST_Request $request) use ($route): array {
                    return $route->handleRestRequest($request);
                },
                'permission_callback' => function () use ($route): bool {
                    return $route->checkPermission();
                },
                'args' => $this->getArguments($route)
            ]
        );
    }

    final private function getArguments(RestRoute $route): array
    {
        $arguments = $route->getArguments();
        $return = [];

        foreach ($arguments as $argument => $config) {
            $sanitizeCallback = null;

            if (is_callable($config['sanitizeCallback'] ?? null)) {
                $sanitizeCallback = $config['sanitizeCallback'];
            } else {
                switch ($config['type'] ?? 'string') {
                    case 'string':
                        $sanitizeCallback = function ($value, WP_REST_Request $request, string $param): string {
                            return sanitize_text_field($value);
                        };
                        break;

                    case 'url':
                        $sanitizeCallback = function ($value, WP_REST_Request $request, string $param): string {
                            return sanitize_url($value);
                        };
                        break;
                }
            }


            $return[$argument] = [
                'required' => $config['required'] ?? false,
                'default' => $config['default'] ?? null
            ];

            if (!is_null($sanitizeCallback)) {
                $return[$argument]['sanitize_callback'] = $sanitizeCallback;
            }
        }

        return $return;
    }
}
