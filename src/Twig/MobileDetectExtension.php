<?php

namespace App\Twig;

use MobileDetectBundle\DeviceDetector\MobileDetector;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MobileDetectExtension extends AbstractExtension
{
    private MobileDetector $mobileDetector;

    public function __construct(MobileDetector $mobileDetector)
    {
        $this->mobileDetector = $mobileDetector;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_mobile', fn() => $this->mobileDetector->isMobile()),
            new TwigFunction('is_tablet', fn() => $this->mobileDetector->isTablet()),
            new TwigFunction('is_desktop', fn() => !$this->mobileDetector->isMobile() && !$this->mobileDetector->isTablet()),
        ];
    }
}
