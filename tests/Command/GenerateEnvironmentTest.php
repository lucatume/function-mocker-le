<?php

namespace tad\FunctionMockerLe\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEnvironmentTest extends TestCase {

    private $data = __DIR__ . '/../data/';

    private $output = __DIR__ . '/../output/';

    /**
     * @test
     */
    public function should_generate_expected_file_with_no_config() {
        /** @var InputInterface $input */
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument('source')->willReturn($this->data . 'functions-1.php');
        $input->getArgument('env')->willReturn( 'EnvironmentOne' );
        $input->getOption('env-path')->willReturn($this->output);
        $input->getOption('config-file')->willReturn(null);
        /** @var OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class);
        $output->writeln(Argument::type('string'))->willReturn(null);

        $sut = new GenerateEnvironment();
        $sut->execute($input->reveal(), $output->reveal());

        $this->assertFileExists($this->output . '/EnvironmentOne.php');
        $this->assertFileExists($this->output . '/EnvironmentOne-functions.php');

        include_once($this->output . '/EnvironmentOne.php');

        $this->assertTrue(class_exists( 'EnvironmentOne' ));

        $one = new \EnvironmentOne();
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
     * It should generate expected file with no config and namespaced environment
     *
     * @test
     */
    public function should_generate_expected_file_with_no_config_and_namespaced_environment() {
        /** @var InputInterface $input */
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument('source')->willReturn($this->data . 'functions-2.php');
        $input->getArgument('env')->willReturn('foo\\bar\\EnvironmentTwo');
        $input->getOption('env-path')->willReturn($this->output);
        $input->getOption('config-file')->willReturn(null);
        /** @var OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class);
        $output->writeln(Argument::type('string'))->willReturn(null);

        $sut = new GenerateEnvironment();
        $sut->execute($input->reveal(), $output->reveal());

        $this->assertFileExists($this->output . '/EnvironmentTwo.php');
        $this->assertFileExists($this->output . '/EnvironmentTwo-functions.php');

        include_once($this->output . '/EnvironmentTwo.php');

        $this->assertTrue(class_exists('\\foo\\bar\\EnvironmentTwo'));

        $one = new \foo\bar\EnvironmentTwo();
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
     * It should generate environment from folder
     *
     * @test
     */
    public function should_generate_environment_from_folder() {
        /** @var InputInterface $input */
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument('source')->willReturn($this->data . 'functions-folder');
        $input->getArgument('env')->willReturn('EnvironmentThree');
        $input->getOption('env-path')->willReturn($this->output);
        $input->getOption('config-file')->willReturn(null);
        /** @var OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class);
        $output->writeln(Argument::type('string'))->willReturn(null);
        $sut = new GenerateEnvironment();
        $sut->execute($input->reveal(), $output->reveal());

        $this->assertFileExists($this->output . '/EnvironmentThree.php');
        $this->assertFileExists($this->output . '/EnvironmentThree-functions.php');

        include_once($this->output . '/EnvironmentThree.php');

        $this->assertTrue(class_exists('\\EnvironmentThree'));

        $one = new \EnvironmentThree();
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
