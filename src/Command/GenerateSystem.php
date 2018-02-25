<?php

namespace tad\FunctionMockerLe\Command;

use PhpParser\Builder\Class_;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use tad\FunctionMockerLe\System;

/**
 * Class GenerateSystem
 *
 * Implements the command to generate a class implementing tad\FucntionMockerLe\System from a source file
 * or files.
 *
 * @package tad\FunctionMockerLe\Command
 */
class GenerateSystem extends Command {
    const NAME = 'system:generate';
    protected $defined = [];

    /**
     * @var Input
     */
    protected $input;

    /**
     * @var Output
     */
    protected $output;

    /**
     * Configures the command.
     */
    protected function configure() {
        $this->setName(self::NAME)
            ->setDescription('Scaffolds a System from source files')
            ->setHelp('This command will parse a specified folder, or file, and generate a System defining the functions found in the source.')
            ->addArgument('source', InputArgument::REQUIRED, 'The source file or folder path, relative to the current working directory.')
            ->addArgument('system', InputArgument::REQUIRED, 'The name, including namespace, of the System class to generate')
            ->addOption('system-path', 'sp', InputOption::VALUE_OPTIONAL, 'The path to the folder that will contain the System file; if the destination does not exist it will be created.', getcwd());
        // @todo compress output to use defineAll
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;

        $source = $input->getArgument('source');

        $this->checkSource($source);

        $src = $this->input->getArgument('source');
        $this->output->writeln("<comment>Reading functions from {$src}...</comment>");

        $functionsAsts = $this->getSourceAsts($source);

        $count = count($this->defined);

        $this->output->writeln("<comment>Found {$count} function(s)</comment>");

        return $this->printSystem($input->getArgument('system'), $input->getOption('system-path'), array_values($functionsAsts));
    }

    /**
     * Checks that the specified source is valid, be it a file or a folder.
     *
     * @param string $source
     */
    protected function checkSource($source) {
        if (!file_exists($source)) {
            throw new \InvalidArgumentException("Source file or folder {$source} does not exist");
        }

        if (!is_readable($source)) {
            throw new \InvalidArgumentException("Source file or folder {$source} is not readable");
        }
    }

    /**
     * Parses the specified source(s) to fetch all the functions defined in the file(s).
     *
     * @param string $source
     * @return array
     */
    protected function getSourceAsts($source) {
        if (!is_dir($source)) {
            return $this->getFileFunctionsAsts(realpath($source));
        }

        $files = [];
        $dirs = [$source];
        while (null !== ($dir = array_pop($dirs))) {
            if ($dh = opendir($dir)) {
                while (false !== ($file = readdir($dh))) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    $path = $dir . '/' . $file;
                    if (is_dir($path)) {
                        $dirs[] = $path;
                    } else {
                        $files[] = $path;
                    }
                }
                closedir($dh);
            }
        }

        $asts = array_map(function ($file) {
            return $this->getFileFunctionsAsts($file);
        }, $files);

        $merged = array_merge(...$asts);

        $this->definedCount = count($merged);
        return $merged;
    }

    /**
     * Parses the specified source file to fetch all the functions defined in the file.
     *
     * @param string $file
     * @return array
     */
    protected function getFileFunctionsAsts($file) {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        try {
            $asts = $parser->parse(file_get_contents($file));
        } catch (\Exception $e) {
            throw new \RuntimeException("Could not parse file {$file} -- {$e->getMessage()}");
        }

        $asts = $this->removeClasses($asts);
        $asts = $this->removeNonFunctions($asts);
        $this->setDefinedFunctions($asts);

        return $this->wrapInFunctionExistsCheck($asts);
    }

    /**
     * Removes any class statement from the list of statements.
     *
     * @param array $ast
     *
     * @return array
     */
    protected function removeClasses(array $ast) {
        return array_filter($ast, function (NodeAbstract $stmt) {
            return !$stmt instanceof Class_;
        });
    }

    /**
     * Removes any non-function statement from the list of statements.
     *
     * @param array $ast
     *
     * @return array
     */
    protected function removeNonFunctions(array $ast) {
        return array_filter($ast, function (NodeAbstract $stmt) {
            return $stmt instanceof Function_;
        });
    }

    /**
     * Parses the list of functions found in the source to set what will be returned
     * by the generated class `defined` method.
     *
     * @param array $asts
     */
    protected function setDefinedFunctions($asts) {
        $this->defined = array_merge($this->defined, array_map(function (Function_ $fun) {
            return new String_($fun->name);
        }, $asts));
    }

    /**
     * Wraps all the functions statements found in the source in `if(function_exists...` checks.
     *
     * @param array $asts
     *
     * @return array
     */
    protected function wrapInFunctionExistsCheck($asts) {
        return array_map(function (Function_ $functionNode) {
            return new If_(new BooleanNot(new FuncCall(new Name('function_exists'), [new Arg(new Scalar\String_($functionNode->name))])), [
                'stmts' => [
                    new FuncCall(new Name('\tad\FunctionMockerLe\define'), [
                        new String_($functionNode->name),
                        new Closure([
                            'params' => $functionNode->params,
                            'stmts' => [
                                new Return_(),
                            ],
                        ]),
                    ]),
                ],
            ]);
        }, $asts);
    }

    /**
     * Prints the class PHP code to file.
     *
     * @param string $systemName
     * @param string $systemPath
     * @param array $functionsAsts
     *
     * @return int
     */
    protected function printSystem($systemName, $systemPath, array $functionsAsts) {
        $systemClassFrags = explode('\\', $systemName);
        $systemClass = array_pop($systemClassFrags);
        $asts = [];
        $builder = new BuilderFactory();
        $date = date('Y-m-d H:i:s');
        $classStmt = $builder->class($systemClass)->implement('\\' . System::class)
            ->setDocComment("/*\n* Auto-generated by the function-mocker-le package with the `fmle generate:system command`\n* on {$date}\n*/")
            ->addStmt($builder->method('name')
                ->makePublic()
                ->addStmt(new Return_(new String_($systemClass)))
            )
            ->addStmt($builder->method('setUp')
                ->makePublic()
                ->addParam(new Param('args', null, null, false, true))
                ->addStmts($functionsAsts)
            )
            ->addStmt(
                $builder->method('tearDown')
                    ->makePublic()
                    ->addStmt(
                        new FuncCall(
                            new Name('\\tad\\FunctionMockerLe\\undefineAll'),
                            [new MethodCall(new Variable('this'), new Name('defined'))]
                        )
                    )
            )
            ->addStmt(
                $builder->method('defined')
                    ->makePublic()
                    ->addStmt(
                        new Return_(new Array_($this->defined))
                    )
            );

        if (count($systemClassFrags)) {
            $asts[] = $builder->namespace(implode('\\', $systemClassFrags))
                ->addStmt($classStmt)
                ->getNode();
        } else {
            $asts[] = $classStmt->getNode();
        }

        $outputFilePath = rtrim($systemPath, DIRECTORY_SEPARATOR) . "/{$systemClass}.php";
        $output = (new PrettyPrinter())->prettyPrintFile($asts);

        $this->output->writeln("<comment>Writing to file {$outputFilePath}</comment>");

        $put = file_put_contents($outputFilePath, $output, LOCK_EX);

        if ($put) {
            $this->output->writeln("<info>Done! Check out the {$outputFilePath} file.</info>");
            return 0;
        }

        $this->output->writeln("<error>Could not write to {$outputFilePath}...</error>");

        return 1;
    }
}
