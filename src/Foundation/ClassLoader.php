<?php
namespace Concrete\Core\Foundation;

use Concrete\Core\Package\Package;
use Concrete\Core\Foundation\Psr4ClassLoader;

/**
 * Provides autoloading for concrete5
 * Typically getInstance() should be used rather than instantiating a new object.
 *
 * \@package Concrete\Core\Foundation
 */
class ClassLoader
{
    /** @var ClassLoader */
    public static $instance;

    /**
     * @var ClassLoaderInterface[]
     */
    protected $loaders;

    protected $enableLegacyNamespace = false;

    protected $applicationNamespace = 'Application';

    /**
     * @return boolean
     */
    public function isEnableLegacyNamespace()
    {
        return $this->enableLegacyNamespace;
    }

    /**
     * @param boolean $enableLegacyNamespace
     */
    public function enableLegacyNamespace()
    {
        $this->enableLegacyNamespace = true;
        $this->disable();
        $this->activateAutoloaders();
        $this->enable();
    }

    protected function activateAutoloaders()
    {
        $this->loaders = array();
        $this->setupLegacyAutoloading();
        $this->setupCoreAutoloading(); // Modified PSR4
        $this->setupCoreSourceAutoloading(); // Strict PSR4
    }

    /**
     * @return string
     */
    public function getApplicationNamespace()
    {
        return $this->applicationNamespace;
    }

    /**
     * @param string $applicationNamespace
     */
    public function setApplicationNamespace($applicationNamespace)
    {
        $this->applicationNamespace = $applicationNamespace;
        $this->disable();
        $this->activateAutoloaders();
        $this->enable();
    }


    public function __construct($enableLegacyNamespace = false, $applicationNamespace = 'Application')
    {
        $this->enableLegacyNamespace = $enableLegacyNamespace;
        $this->applicationNamespace = $applicationNamespace;
        $this->activateAutoloaders();
        $this->enableAliasClassAutoloading();
    }

    /**
     * Aliases concrete5 classes to shorter class name aliases.
     *
     * IDEs will not recognize these classes by default. A symbols file can be generated to
     * assist IDEs by running SymbolGenerator::render() via PHP or executing the command-line
     * 'concrete/bin/concrete5 c5:ide-symbols
     */
    protected function enableAliasClassAutoloading()
    {
        $list = ClassAliasList::getInstance();
        $loader = new AliasClassLoader($list);
        $loader->register(); // We can't add this to the loaders array because there's no way to unregister these once they're registered
    }

    protected function setupLegacyAutoloading()
    {
        $mapping = array(
            'Loader' => DIR_BASE_CORE . '/' . DIRNAME_CLASSES . '/Legacy/Loader.php',
            'TaskPermission' => DIR_BASE_CORE . '/' . DIRNAME_CLASSES . '/Legacy/TaskPermission.php',
            'FilePermissions' => DIR_BASE_CORE . '/' . DIRNAME_CLASSES . '/Legacy/FilePermissions.php',
        );

        $loader = new MapClassLoader($mapping);
        $this->loaders[] = $loader;
    }

    protected function setupCoreAutoloading()
    {
        $loader = new ModifiedPSR4ClassLoader();
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\StartingPointPackage',
            DIR_BASE_CORE . '/config/install/' . DIRNAME_PACKAGES);
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Attribute', DIR_BASE_CORE . '/' . DIRNAME_ATTRIBUTES);
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\MenuItem', DIR_BASE_CORE . '/' . DIRNAME_MENU_ITEMS);
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Authentication',
            DIR_BASE_CORE . '/' . DIRNAME_AUTHENTICATION);
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Block', DIR_BASE_CORE . '/' . DIRNAME_BLOCKS);
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Theme', DIR_BASE_CORE . '/' . DIRNAME_THEMES);
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Controller\\PageType',
            DIR_BASE_CORE . '/' . DIRNAME_CONTROLLERS . '/' . DIRNAME_PAGE_TYPES);
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Controller', DIR_BASE_CORE . '/' . DIRNAME_CONTROLLERS);
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Job', DIR_BASE_CORE . '/' . DIRNAME_JOBS);
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Express',
            DIR_APPLICATION . '/config/express/Entity/Concrete/Express');

        $loader->addPrefix($this->getApplicationNamespace() . '\\StartingPointPackage',
            DIR_APPLICATION . '/config/install/' . DIRNAME_PACKAGES);
        $loader->addPrefix($this->getApplicationNamespace() . '\\Attribute', DIR_APPLICATION . '/' . DIRNAME_ATTRIBUTES);
        $loader->addPrefix($this->getApplicationNamespace() . '\\MenuItem', DIR_APPLICATION . '/' . DIRNAME_MENU_ITEMS);
        $loader->addPrefix($this->getApplicationNamespace() . '\\Authentication', DIR_APPLICATION . '/' . DIRNAME_AUTHENTICATION);
        $loader->addPrefix($this->getApplicationNamespace() . '\\Block', DIR_APPLICATION . '/' . DIRNAME_BLOCKS);
        $loader->addPrefix($this->getApplicationNamespace() . '\\Theme', DIR_APPLICATION . '/' . DIRNAME_THEMES);
        $loader->addPrefix($this->getApplicationNamespace() . '\\Controller\\PageType',
            DIR_APPLICATION . '/' . DIRNAME_CONTROLLERS . '/' . DIRNAME_PAGE_TYPES);
        $loader->addPrefix($this->getApplicationNamespace() . '\\Controller', DIR_APPLICATION . '/' . DIRNAME_CONTROLLERS);
        $loader->addPrefix($this->getApplicationNamespace() . '\\Job', DIR_APPLICATION . '/' . DIRNAME_JOBS);
        $this->loaders[] = $loader;
    }

    public function setupCoreSourceAutoloading()
    {
        $loader = new Psr4ClassLoader();
        $loader->addPrefix(NAMESPACE_SEGMENT_VENDOR . '\\Core', DIR_BASE_CORE . '/' . DIRNAME_CLASSES);

        // Handle class core extensions like antispam and captcha with Application\Concrete\MyCaptchaLibrary
        $loader->addPrefix($this->getApplicationNamespace() . '\\Concrete', DIR_APPLICATION . '/' . DIRNAME_CLASSES . '/Concrete');

        // Application entities
        $loader->addPrefix($this->getApplicationNamespace() . '\\Entity', DIR_APPLICATION . '/' . DIRNAME_CLASSES . '/Entity');

        if ($this->enableLegacyNamespace) {
            $loader->addPrefix($this->getApplicationNamespace() . '\\Src', DIR_APPLICATION . '/' . DIRNAME_CLASSES);
        }

        $this->loaders[] = $loader;
    }



    /**
     * Returns the ClassLoader instance.
     *
     * @return ClassLoader
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    public function enable()
    {
        foreach ($this->loaders as $loader) {
            $loader->register();
        }
    }

    public function disable()
    {
        foreach ($this->loaders as $loader) {
            $loader->unregister();
        }
    }

}
