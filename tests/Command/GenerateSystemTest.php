<?php

namespace tad\FunctionMockerLe\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSystemTest extends TestCase {

    private $data = __DIR__ . '/../data/';

    private $output = __DIR__ . '/../output/';

    /**
     * @test
     */
    public function should_generate_expected_file_with_no_config() {
        /** @var InputInterface $input */
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument('source')->willReturn($this->data . 'functions-1.php');
        $input->getArgument('system')->willReturn('SystemOne');
        $input->getOption('system-path')->willReturn($this->output);
        $input->getOption('config-file')->willReturn(null);
        /** @var OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class);
        $output->writeln(Argument::type('string'))->willReturn(null);

        $sut = new GenerateSystem();
        $sut->execute($input->reveal(), $output->reveal());

        $this->assertFileExists($this->output . '/SystemOne.php');
        $this->assertFileExists($this->output . '/SystemOne-functions.php');

        include_once($this->output . '/SystemOne.php');

        $this->assertTrue(class_exists('SystemOne'));

        $one = new \SystemOne();
        $one->setUp();

        $this->assertTrue(function_exists('aFunction'));
        $fr = new \ReflectionFunction('aFunction');
        $parameters = $fr->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('arg1', $parameters[0]->getName());
        $this->assertEquals('array', $parameters[0]->getType());
        $this->assertEquals('string2', $parameters[1]->getName());
        $this->assertFalse($parameters[1]->hasType());
    }

    /**
     * It should generate expected file with no config and namespaced system
     *
     * @test
     */
    public function should_generate_expected_file_with_no_config_and_namespaced_system() {
        /** @var InputInterface $input */
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument('source')->willReturn($this->data . 'functions-2.php');
        $input->getArgument('system')->willReturn('foo\\bar\\SystemTwo');
        $input->getOption('system-path')->willReturn($this->output);
        $input->getOption('config-file')->willReturn(null);
        /** @var OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class);
        $output->writeln(Argument::type('string'))->willReturn(null);

        $sut = new GenerateSystem();
        $sut->execute($input->reveal(), $output->reveal());

        $this->assertFileExists($this->output . '/SystemTwo.php');
        $this->assertFileExists($this->output . '/SystemTwo-functions.php');

        include_once($this->output . '/SystemTwo.php');

        $this->assertTrue(class_exists('\\foo\\bar\\SystemTwo'));

        $one = new \foo\bar\SystemTwo();
        $one->setUp();

        $this->assertTrue(function_exists('bFunction'));
        $fr = new \ReflectionFunction('bFunction');
        $parameters = $fr->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('arg1', $parameters[0]->getName());
        $this->assertEquals('array', $parameters[0]->getType());
        $this->assertEquals('string2', $parameters[1]->getName());
        $this->assertFalse($parameters[1]->hasType());
    }

    /**
     * It should generate system from folder
     *
     * @test
     */
    public function should_generate_system_from_folder() {
        /** @var InputInterface $input */
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument('source')->willReturn($this->data . 'functions-folder');
        $input->getArgument('system')->willReturn('SystemThree');
        $input->getOption('system-path')->willReturn($this->output);
        $input->getOption('config-file')->willReturn(null);
        /** @var OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class);
        $output->writeln(Argument::type('string'))->willReturn(null);
        $sut = new GenerateSystem();
        $sut->execute($input->reveal(), $output->reveal());

        $this->assertFileExists($this->output . '/SystemThree.php');
        $this->assertFileExists($this->output . '/SystemThree-functions.php');

        include_once($this->output . '/SystemThree.php');

        $this->assertTrue(class_exists('\\SystemThree'));

        $one = new \SystemThree();
        $one->setUp();

        foreach ([
            'testFunction123',
            'testFunction124',
            'testFunction125',
            'testFunction126',
            'testFunction127',
            '\\salty\\dogs\\testFunction125',
            '\\salty\\dogs\\testFunction126',
            '\\salty\\dogs\\testFunction127',
        ] as $functionName){
            $this->assertTrue(function_exists($functionName),"Function {$functionName} does not exist.");
        }
    }
}
