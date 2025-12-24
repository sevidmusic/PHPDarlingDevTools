<?php

declare(strict_types=1);

namespace __ROOT_NAMESPACE__\tests\interfaces\__SUB_NAMESPACE__;

use __ROOT_NAMESPACE__\interfaces\__SUB_NAMESPACE__\__TARGET_CLASS_NAME__;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * The __TARGET_CLASS_NAME__TestTrait defines common tests for
 * implementations of the __TARGET_CLASS_NAME__ interface.
 *
 * @see __TARGET_CLASS_NAME__
 */
#[CoversClass(__TARGET_CLASS_NAME__::class)]
trait __TARGET_CLASS_NAME__TestTrait
{
    /**
     * @var __TARGET_CLASS_NAME__
     *                            An instance of a
     *                            __TARGET_CLASS_NAME__
     *                            implementation to test
     */
    protected __TARGET_CLASS_NAME__ $__LC_TARGET_CLASS_NAME__;

    /**
     * Return the __TARGET_CLASS_NAME__ implementation instance to test.
     */
    protected function __LC_TARGET_CLASS_NAME__TestInstance(): __TARGET_CLASS_NAME__
    {
        return $this->__LC_TARGET_CLASS_NAME__;
    }

    /**
     * Set up an instance of a __TARGET_CLASS_NAME__ implementation to test.
     *
     * This method must set the __TARGET_CLASS_NAME__ implementation instance
     * to be tested via the set__TARGET_CLASS_NAME__TestInstance() method.
     *
     * This method may also be used to perform any additional setup
     * required by the implementation being tested.
     *
     * @example
     *
     * ```
     * protected function setUp(): void
     * {
     *     $this->set__TARGET_CLASS_NAME__TestInstance(
     *         new \__ROOT_NAMESPACE__\classes\__SUB_NAMESPACE__\__TARGET_CLASS_NAME__()
     *     );
     * }
     *
     * ```
     */
    abstract protected function setUp(): void;

    /**
     * Set the __TARGET_CLASS_NAME__ implementation instance to test.
     *
     * @param __TARGET_CLASS_NAME__ $__LC_TARGET_CLASS_NAME__TestInstance
     *                                                                    An instance of an
     *                                                                    implementation of
     *                                                                    the __TARGET_CLASS_NAME__
     *                                                                    interface to test
     */
    protected function set__TARGET_CLASS_NAME__TestInstance(
        __TARGET_CLASS_NAME__ $__LC_TARGET_CLASS_NAME__TestInstance
    ): void {
        $this->__LC_TARGET_CLASS_NAME__ = $__LC_TARGET_CLASS_NAME__TestInstance;
    }
}
