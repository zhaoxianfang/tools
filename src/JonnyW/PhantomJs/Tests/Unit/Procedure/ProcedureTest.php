<?php

/*
 * This file is part of the php-phantomjs.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace zxf\JonnyW\PhantomJs\Tests\Unit\Procedure;

use Twig_Environment;
use Twig_Loader_String;
use zxf\JonnyW\PhantomJs\Engine;
use zxf\JonnyW\PhantomJs\Cache\FileCache;
use zxf\JonnyW\PhantomJs\Cache\CacheInterface;
use zxf\JonnyW\PhantomJs\Parser\JsonParser;
use zxf\JonnyW\PhantomJs\Parser\ParserInterface;
use zxf\JonnyW\PhantomJs\Template\TemplateRenderer;
use zxf\JonnyW\PhantomJs\Template\TemplateRendererInterface;
use zxf\JonnyW\PhantomJs\Procedure\Input;
use zxf\JonnyW\PhantomJs\Procedure\Output;
use zxf\JonnyW\PhantomJs\Procedure\Procedure;

/**
 * PHP PhantomJs
 *
 * @author Jon Wenmoth <contact@jonnyw.me>
 */
class ProcedureTest extends \PHPUnit_Framework_TestCase
{

/** +++++++++++++++++++++++++++++++++++ **/
/** ++++++++++++++ TESTS ++++++++++++++ **/
/** +++++++++++++++++++++++++++++++++++ **/

    /**
     * Test procedure template can be
     * set in procedure
     *
     * @access public
     * @return void
     */
    public function testProcedureTemplateCanBeSetInProcedure()
    {
        $template = 'PROCEDURE_TEMPLATE';

        $engne     = $this->getEngine();
        $parser    = $this->getParser();
        $cache     = $this->getCache();
        $renderer  = $this->getRenderer();

        $procedure = $this->getProcedure($engne, $parser, $cache, $renderer);
        $procedure->setTemplate($template);

        $this->assertSame($procedure->getTemplate(), $template);
    }

    /**
     * Test procedure can be compiled.
     *
     * @access public
     * @return void
     */
    public function testProcedureCanBeCompiled()
    {
        $template = 'TEST_{{ input.get("uncompiled") }}_PROCEDURE';

        $engne     = $this->getEngine();
        $parser    = $this->getParser();
        $cache     = $this->getCache();
        $renderer  = $this->getRenderer();

        $input  = $this->getInput();
        $input->set('uncompiled', 'COMPILED');

        $procedure = $this->getProcedure($engne, $parser, $cache, $renderer);
        $procedure->setTemplate($template);

        $this->assertSame('TEST_COMPILED_PROCEDURE', $procedure->compile($input));
    }

    /**
     * Test not writable exception is thrown if procedure
     * script cannot be written to file
     *
     * @access public
     * @return void
     */
    public function testNotWritableExceptionIsThrownIfProcedureScriptCannotBeWrittenToFile()
    {
        $this->setExpectedException('zxf\JonnyW\PhantomJs\Exception\NotWritableException');

        $engne    = $this->getEngine();
        $parser   = $this->getParser();
        $renderer = $this->getRenderer();

        $cache = $this->getCache('/an/invalid/dir');

        $input  = $this->getInput();
        $output = $this->getOutput();

        $procedure = $this->getProcedure($engne, $parser, $cache, $renderer);
        $procedure->run($input, $output);
    }

    /**
     * Test procedure failed exception is thrown if procedure
     * cannot be run.
     *
     * @access public
     * @return void
     */
    public function testProcedureFailedExceptionIsThrownIfProcedureCannotBeRun()
    {
        $this->setExpectedException('zxf\JonnyW\PhantomJs\Exception\ProcedureFailedException');

        $parser   = $this->getParser();
        $cache    = $this->getCache();
        $renderer = $this->getRenderer();
        $input    = $this->getInput();
        $output   = $this->getOutput();

        $engne = $this->getEngine();
        $engne->method('getCommand')
            ->will($this->throwException(new \Exception()));

        $procedure = $this->getProcedure($engne, $parser, $cache, $renderer);
        $procedure->run($input, $output);
    }

/** +++++++++++++++++++++++++++++++++++ **/
/** ++++++++++ TEST ENTITIES ++++++++++ **/
/** +++++++++++++++++++++++++++++++++++ **/

    /**
     * Get procedure instance.
     *
     * @access protected
     * @param  \JonnyW\PhantomJs\Engine                             $engine
     * @param  \JonnyW\PhantomJs\Parser\ParserInterface             $parser
     * @param  \JonnyW\PhantomJs\Cache\CacheInterface               $cacheHandler
     * @param  \JonnyW\PhantomJs\Template\TemplateRendererInterface $renderer
     * @return \JonnyW\PhantomJs\Procedure\Procedure
     */
    protected function getProcedure(Engine $engine, ParserInterface $parser, CacheInterface $cacheHandler, TemplateRendererInterface $renderer)
    {
        $procedure = new Procedure($engine, $parser, $cacheHandler, $renderer);

        return $procedure;
    }

    /**
     * Get parser.
     *
     * @access protected
     * @return \JonnyW\PhantomJs\Parser\JsonParser
     */
    protected function getParser()
    {
        $parser = new JsonParser();

        return $parser;
    }

    /**
     * Get cache.
     *
     * @access protected
     * @param  string                            $cacheDir  (default: '')
     * @param  string                            $extension (default: 'proc')
     * @return \JonnyW\PhantomJs\Cache\FileCache
     */
    protected function getCache($cacheDir = '', $extension = 'proc')
    {
        $cache = new FileCache(($cacheDir ? $cacheDir : sys_get_temp_dir()), 'proc');

        return $cache;
    }

    /**
     * Get template renderer.
     *
     * @access protected
     * @return \JonnyW\PhantomJs\Template\TemplateRenderer
     */
    protected function getRenderer()
    {
        $twig = new Twig_Environment(
            new Twig_Loader_String()
        );

        $renderer = new TemplateRenderer($twig);

        return $renderer;
    }

    /**
     * Get input
     *
     * @access protected
     * @return \JonnyW\PhantomJs\Procedure\Input
     */
    protected function getInput()
    {
        $input = new Input();

        return $input;
    }

    /**
     * Get output.
     *
     * @access protected
     * @return \JonnyW\PhantomJs\Procedure\Output
     */
    protected function getOutput()
    {
        $output = new Output();

        return $output;
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
}
