<?php

/*
 * This file is part of the php-phantomjs.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace zxf\JonnyW\PhantomJs\Tests\Unit;

use zxf\JonnyW\PhantomJs\Client;
use zxf\JonnyW\PhantomJs\Engine;
use zxf\JonnyW\PhantomJs\Http\MessageFactoryInterface;
use zxf\JonnyW\PhantomJs\Procedure\ProcedureLoaderInterface;
use zxf\JonnyW\PhantomJs\Procedure\ProcedureCompilerInterface;

/**
 * PHP PhantomJs
 *
 * @author Jon Wenmoth <contact@jonnyw.me>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{

/** +++++++++++++++++++++++++++++++++++ **/
/** ++++++++++++++ TESTS ++++++++++++++ **/
/** +++++++++++++++++++++++++++++++++++ **/

    /**
     * Test can get client through
     * factory method.
     *
     * @access public
     * @return void
     */
    public function testCanGetClientThroughFactoryMethod()
    {
        $this->assertInstanceOf('zxf\JonnyW\PhantomJs\Client', Client::getInstance());
    }

    /**
     * Test can get engine.
     *
     * @return void
     */
    public function testCanGetEngne()
    {
        $engine             = $this->getEngine();
        $procedureLoader    = $this->getProcedureLoader();
        $procedureCompiler  = $this->getProcedureCompiler();
        $messageFactory     = $this->getMessageFactory();

        $client = $this->getClient($engine, $procedureLoader, $procedureCompiler, $messageFactory);

        $this->assertInstanceOf('zxf\JonnyW\PhantomJs\Engine', $client->getEngine());
    }

    /**
     * Test can get message factory
     *
     * @return void
     */
    public function testCanGetMessageFactory()
    {
        $engine             = $this->getEngine();
        $procedureLoader    = $this->getProcedureLoader();
        $procedureCompiler  = $this->getProcedureCompiler();
        $messageFactory     = $this->getMessageFactory();

        $client = $this->getClient($engine, $procedureLoader, $procedureCompiler, $messageFactory);

        $this->assertInstanceOf('zxf\JonnyW\PhantomJs\Http\MessageFactoryInterface', $client->getMessageFactory());
    }

    /**
     * Test can get procedure loader.
     *
     * @return void
     */
    public function testCanGetProcedureLoader()
    {
        $engine             = $this->getEngine();
        $procedureLoader    = $this->getProcedureLoader();
        $procedureCompiler  = $this->getProcedureCompiler();
        $messageFactory     = $this->getMessageFactory();

        $client = $this->getClient($engine, $procedureLoader, $procedureCompiler, $messageFactory);

        $this->assertInstanceOf('zxf\JonnyW\PhantomJs\Procedure\ProcedureLoaderInterface', $client->getProcedureLoader());
    }

/** +++++++++++++++++++++++++++++++++++ **/
/** ++++++++++ TEST ENTITIES ++++++++++ **/
/** +++++++++++++++++++++++++++++++++++ **/

    /**
     * Get client instance
     *
     * @param  \JonnyW\PhantomJs\Engine                               $engine
     * @param  \JonnyW\PhantomJs\Procedure\ProcedureLoaderInterface   $procedureLoader
     * @param  \JonnyW\PhantomJs\Procedure\ProcedureCompilerInterface $procedureCompiler
     * @param  \JonnyW\PhantomJs\Http\MessageFactoryInterface         $messageFactory
     * @return \JonnyW\PhantomJs\Client
     */
    protected function getClient(Engine $engine, ProcedureLoaderInterface $procedureLoader, ProcedureCompilerInterface $procedureCompiler, MessageFactoryInterface $messageFactory)
    {
        $client = new Client($engine, $procedureLoader, $procedureCompiler, $messageFactory);

        return $client;
    }

/** +++++++++++++++++++++++++++++++++++ **/
/** ++++++++++ MOCKS / STUBS ++++++++++ **/
/** +++++++++++++++++++++++++++++++++++ **/

    /**
     * Get engine
     *
     * @access protected
     * @return \JonnyW\PhantomJs\Engine
     */
    protected function getEngine()
    {
        $engine = $this->getMock('zxf\JonnyW\PhantomJs\Engine');

        return $engine;
    }

    /**
     * Get message factory
     *
     * @access protected
     * @return \JonnyW\PhantomJs\Http\MessageFactoryInterface
     */
    protected function getMessageFactory()
    {
        $messageFactory = $this->getMock('zxf\JonnyW\PhantomJs\Http\MessageFactoryInterface');

        return $messageFactory;
    }

    /**
     * Get procedure loader.
     *
     * @access protected
     * @return \JonnyW\PhantomJs\Procedure\ProcedureLoaderInterface
     */
    protected function getProcedureLoader()
    {
        $procedureLoader = $this->getMock('zxf\JonnyW\PhantomJs\Procedure\ProcedureLoaderInterface');

        return $procedureLoader;
    }

    /**
     * Get procedure validator.
     *
     * @access protected
     * @return \JonnyW\PhantomJs\Procedure\ProcedureCompilerInterface
     */
    protected function getProcedureCompiler()
    {
        $procedureCompiler = $this->getMock('zxf\JonnyW\PhantomJs\Procedure\ProcedureCompilerInterface');

        return $procedureCompiler;
    }
}
