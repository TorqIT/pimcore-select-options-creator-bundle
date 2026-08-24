<?php

declare(strict_types=1);

namespace Pimcore\Extension\Bundle {
    if (!interface_exists(PimcoreBundleAdminClassicInterface::class)) {
        interface PimcoreBundleAdminClassicInterface {}
    }
}

namespace TorqIT\SelectOptionsCreatorBundle {
    use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
    use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;

    class SelectOptionsCreatorBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
    {
        public function getCssPaths(): array
        {
            return [];
        }

        public function getJsPaths(): array
        {
            return [];
        }

        public function getEditmodeJsPaths(): array
        {
            return [];
        }

        public function getEditmodeCssPaths(): array
        {
            return [];
        }
    }
}
