<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap;

/**
 * Handle Errors that might occur during execution of ldap_*-functions
 *
 * @package Zend\Ldap\ErrorHandler
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var ErrorHandlerInterface The Errror-Handler instance
     */
    protected static $errorHandler;

    /**
     * Start the Error-Handling
     *
     * You can specify which errors to handle by passing a combination of PHPs
     * Error-constants like E_WARNING or E_NOTICE or E_WARNING ^ E_DEPRECATED
     *
     * @param int $level The Error-level(s) to handle by this ErrorHandler
     *
     * @return void
     */
    public static function start($level = E_WARNING)
    {
        self::getErrorHandler()->startErrorHandling($level);
    }

    /**
     * @param bool|false $throw
     *
     * @return mixed
     */
    public static function stop($throw = false)
    {
        return self::getErrorHandler()->stopErrorHandling($throw);
    }

    /**
     * Get an error handler
     *
     * @return ErrorHandlerInterface
     */
    protected static function getErrorHandler()
    {
        if (! self::$errorHandler && ! self::$errorHandler instanceof ErrorHandlerInterface) {
            self::$errorHandler = new self();
        }

        return self::$errorHandler;
    }

    /**
     * This method does nothing on purpose.
     *
     * @param int $level
     *
     * @see ErrorHandlerInterface::startErrorHandling()
     * @return void
     */
    public function startErrorHandling($level = E_WARNING)
    {
        set_error_handler(function ($errNo, $errString) {
        });
    }

    /**
     * This method does nothing on purpose.
     *
     * @param bool|false $throw
     *
     * @see ErrorHandlerInterface::stopErrorHandling()
     * @return void
     */
    public function stopErrorHandling($throw = false)
    {
        restore_error_handler();
    }

    /**
     * Set the error handler to be used
     *
     * @param ErrorHandlerInterface $errorHandler
     *
     * @return void
     */
    public static function setErrorHandler(ErrorHandlerInterface $errorHandler)
    {
        self::$errorHandler = $errorHandler;
    }
}
