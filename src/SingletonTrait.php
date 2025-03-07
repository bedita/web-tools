<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2018 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools;

/**
 * Singleton class.
 *
 * @see https://github.com/sebastianbergmann/phpunit/blob/8.5/tests/_files/Singleton.php
 */
trait SingletonTrait
{
    /**
     * Singleton instance.
     *
     * @var static|null
     */
    private static $uniqueInstance = null;

    /**
     * Singleton constructor.
     *
     * The constructor is declared private in order to
     * prevent new instances from being created.
     *
     * @codeCoverageIgnore
     */
    final protected function __construct()
    {
    }

    /**
     * Singleton clone method.
     *
     * This method is declared private in order to
     * prevent existing instances from being cloned.
     *
     * @return void
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * Singleton getter.
     *
     * Use this method in order to get the singleton instance
     *
     * @return static|null
     */
    final public static function getInstance(): ?static
    {
        if (self::$uniqueInstance === null) {
            self::$uniqueInstance = new static();
        }

        return self::$uniqueInstance;
    }
}
