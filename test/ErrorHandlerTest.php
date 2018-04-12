<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Ldap;

use PHPUnit\Framework\TestCase;
use Zend\Ldap\ErrorHandler;

/**
 * @group      Zend_Ldap
 */
class ErrorHandlerTest extends TestCase
{
    protected $dummyErrorHandler;

    protected $currentErrorHandler = [
        \PHPUnit\Util\ErrorHandler::class,
        'handleError',
    ];

    public function setUp()
    {
        /** @todo: remove when migrate to PHP 7.1+ and PHPUnit 7+ only */
        if (class_exists(\PHPUnit_Util_ErrorHandler::class)) {
            $this->currentErrorHandler[0] = \PHPUnit_Util_ErrorHandler::class;
        }

        $this->dummyErrorHandler = function ($errno, $error) {
        };
    }
    public function testErrorHandlerSettingWorks()
    {
        $errorHandler = new ErrorHandler();

        $this->assertEquals($this->currentErrorHandler, set_error_handler($this->dummyErrorHandler));
        $errorHandler->startErrorHandling();
        $this->assertEquals($this->dummyErrorHandler, set_error_handler($this->dummyErrorHandler));

        restore_error_handler();
        restore_error_handler();
    }

    public function testErrorHandlerREmovalWorks()
    {
        $errorHandler = new ErrorHandler();

        $this->assertEquals($this->currentErrorHandler, set_error_handler($this->dummyErrorHandler));
        $errorHandler->stopErrorHandling();
        $this->assertEquals($this->currentErrorHandler, set_error_handler($this->dummyErrorHandler));

        restore_error_handler();
    }
}
