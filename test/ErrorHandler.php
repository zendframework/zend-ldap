<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Ldap;

use Zend\Ldap\ErrorHandlerInterface;
use Zend\Stdlib\ErrorHandler as DefaultErrorHandler;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * Start the ErrorHandling-process
     *
     * @param int $level
     *
     * @return void
     */
    public function startErrorHandling($level = E_WARNING)
    {
        DefaultErrorHandler::start($level);
    }

    /**
     * Stop the error-handling process.
     * The parameter <var>$throw</var> handles whether the captured errors shall
     * be thrown as Exceptions or not
     *
     * @param bool|false $throw
     *
     * @return mixed
     */
    public function stopErrorHandling($throw = false)
    {
        return DefaultErrorHandler::stop($throw);
    }
}