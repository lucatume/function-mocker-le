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
        $input->getArgument('system')->willReturn('Foo');
        $input->getOption('system-path')->willReturn($this->output);
        $input->getOption('config-file')->willReturn(null);
        /** @var OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class);
        $output->writeln(Argument::type('string'))->willReturn(null);

        $sut = new GenerateSystem();
        $sut->execute($input->reveal(), $output->reveal());

        $this->assertFileExists($this->output . '/Foo.php');
    }
}
