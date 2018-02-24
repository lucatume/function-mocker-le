<?php

namespace tad\FunctionMockerLe\Command;


use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSystem extends Command {

  const NAME = 'system:generate';

  protected function configure() {
    $this->setName(self::NAME)
      ->setDescription('Scaffolds a System from source files')
      ->setHelp('This command will parse a specified folder, or file, and generate a System defining the functions found in the source.')
      ->addArgument('source', InputArgument::REQUIRED, 'The source file or folder path, relative to the current working directory.')
      ->addArgument('system', InputArgument::REQUIRED, 'The name, including namespace, of the System class to generate')
      ->addOption('system-path', 'sp', InputOption::VALUE_OPTIONAL, 'The path to the folder that will contain the System file; if the destination does not exist it will be created.', getcwd());
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $source = $input->getArgument('source');

    $this->checkSource($source);

    $functionsAsts = $this->getSourceAsts($source);

    $this->printSystem($input->getArgument('system'), $input->getArgument('system-path'), $functionsAsts);

    return 0;
  }

  /**
   * @param $source
   */
  protected function checkSource($source) {
    if (!file_exists($source)) {
      throw new \InvalidArgumentException("Source file or folder {$source} does not exist");
    }

    if (!is_readable($source)) {
      throw new \InvalidArgumentException("Source file or folder {$source} is not readable");
    }
  }

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

    return array_merge(...$asts);
  }

  /**
   * @param $file
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

    return $this->wrapInFunctionExistsCheck($asts);
  }

  /**
   * @param $ast
   *
   * @return array
   */
  protected function removeClasses(array $ast) {
    return array_filter($ast, function (NodeAbstract $stmt) {
      return !$stmt instanceof Class_;
    });
  }

  protected function removeNonFunctions(array $ast) {
    return array_filter($ast, function (NodeAbstract $stmt) {
      return $stmt instanceof Function_;
    });
  }

  /**
   * @param $asts
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
              'stmts'  => [
                new Return_(),
              ],
            ]),
          ]),
        ],
      ]);
    }, $asts);
  }

  protected function printSystem($system, $systemPath, array $functionsAsts) {
    $printer = new PrettyPrinter();
    echo $printer->prettyPrintFile(array_values($functionsAsts));
  }
}