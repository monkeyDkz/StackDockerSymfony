<?php

namespace App\Twig;

use Pentatrion\ViteBundle\Service\EntrypointRenderer;
use Symfony\Component\HttpFoundation\RequestStack;

class AutoInjectAssetsExtension extends \Twig\Extension\AbstractExtension
{
    public function __construct(private readonly RequestStack $requestStack, private readonly EntrypointRenderer $entrypointRenderer)
    {
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('autoInjectAssets', [$this, 'autoInjectAssets'], ['is_safe' => ['html']]),
        ];
    }

    public function autoInjectAssets($type)
    {
        if($type === 'css') {
            $method = 'renderLinks';
            $options = [];
        } elseif($type === 'js') {
            $method = 'renderScripts';
            $options = ['defer' => true];
        }

        // get route name
        $routeName = $this->requestStack->getCurrentRequest()->attributes->get('_route');
        $entrypoint = $routeName . '_' . $type;

        try {
            $assets = $this->entrypointRenderer->$method($entrypoint, $options);
            dump(['route' => $routeName, 'type' => $type, 'entrypoint' => $entrypoint, 'assets' => $assets]);
        } catch (\Exception $e) {
            $assets = null;
        }

        return $assets;
    }
}
