<?php
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'WeltPixel_GoogleTagManager',
    __DIR__
);

#if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Model/Config/Source/License/License.php')) {
#    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Model/Config/Source/License/License.php');
#}
