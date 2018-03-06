<?php

namespace tad\FunctionMockerLe\Command;

use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
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
use tad\FunctionMockerLe\Environment;

/**
 * Class GenerateEnvironment
 *
 * Implements the command to generate a class implementing
 * tad\FucntionMockerLe\Environment from a source file or files.
 *
 * @package tad\FunctionMockerLe\Command
 */
class GenerateEnvironment extends Command {

	const NAME = 'env:generate';

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
	 * @var
	 */
	protected $config
		= [
			'include-paths'     => [], 'exclude-paths' => [],
			'exclude-functions' => [],
		];

	/**
	 * @var bool
	 */
	protected $hasConfig = false;

	/**
	 * Executes the command.
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int|null
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
		$this->input  = $input;
		$this->output = $output;

		$source = $input->getArgument('source');
		$this->checkSource($source);
		$this->parseConfiguration($input->getOption('config-file'));

		$this->output->writeln(
			"<comment>Reading functions from {$source}...</comment>"
		);

		$functionsAsts = $this->getSourceAsts($source);
		$count         = count($this->defined);

		$this->output->writeln("<comment>Found {$count} function(s)</comment>");

		return $this->printEnvironment(
			$input->getArgument('env'), $input->getOption('env-path'),
			array_values($functionsAsts)
		);
	}

	/**
	 * Checks that the specified source is valid, be it a file or a folder.
	 *
	 * @param string $source
	 * @param string $type
	 */
	protected function checkSource($source, $type = 'Source') {
		if ( ! file_exists($source)) {
			throw new \InvalidArgumentException(
				"{$type} file or folder {$source} does not exist"
			);
		}

		if ( ! is_readable($source)) {
			throw new \InvalidArgumentException(
				"{$type} file or folder {$source} is not readable"
			);
		}
	}

	/**
	 * Parses the specified configuration file.
	 *
	 * @param string $configFile
	 */
	protected function parseConfiguration($configFile) {
		if (empty($configFile)) {
			$this->output->writeln(
				'<comment>No file configuration provided.</comment>'
			);

			return;
		}

		$this->checkSource($configFile, 'Configuration');

		$this->output->writeln(
			"<comment>Reading configuration from {$configFile}...</comment>"
		);

		$this->config    = array_merge(
			json_decode(file_get_contents($configFile))
		);
		$this->hasConfig = true;
	}

	/**
	 * Parses the specified source(s) to fetch all the functions defined in the
	 * file(s).
	 *
	 * @param string $source
	 *
	 * @return array
	 */
	protected function getSourceAsts($source) {
		if ( ! is_dir($source)) {
			return $this->getSourceAstsFromSingleFile($source);
		}

		return $this->getSourceAstsFromFolder($source);
	}

	/**
	 * @param $source
	 *
	 * @return array
	 */
	protected function getSourceAstsFromSingleFile($source) {
		if ($this->hasConfig) {
			$this->output->writeln(
				'<comment>Since the source is a file the configuration `include-paths`and `exclude-paths` parameters will be ignored.</comment>'
			);
		}

		return $this->getFileFunctionsAsts(realpath($source));
	}

	/**
	 * Parses the specified source file to fetch all the functions defined in
	 * the file.
	 *
	 * @param string $file
	 *
	 * @return array
	 */
	protected function getFileFunctionsAsts($file) {
		$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
		try {
			$asts = $parser->parse(file_get_contents($file));
		} catch (\Exception $e) {
			throw new \RuntimeException(
				"Could not parse file {$file} -- {$e->getMessage()}"
			);
		}

		$asts = $this->extractNamespaceFunctions($asts);
		$asts = $this->removeClasses($asts);
		$asts = $this->removeNonFunctions($asts);
		$asts = $this->removeExcludedFunctions($asts);

		$this->setDefinedFunctions($asts);

		$asts = array_map(
			function (Stmt $node) {
				$functionDefintionStmts = [
					new Return_(
						new FuncCall(
							new Name('\\tad\\FunctionMockerLe\\callback'), [
							new Scalar\MagicConst\Function_(),
							new FuncCall(new Name('func_get_args')),
						]
						)
					),
				];

				if ($node instanceof Function_) {
					$node->stmts = $functionDefintionStmts;
				} elseif ($node instanceof Namespace_) {
					$node->stmts = array_map(
						function (Function_ $function) use (
							$functionDefintionStmts
						) {
							$function->stmts = $functionDefintionStmts;

							return $function;
						}, $node->stmts
					);
				}

				return $node;
			}, $asts
		);


		return $asts;
	}

	protected function extractNamespaceFunctions($asts) {
		foreach ($asts as $ast) {
			if ( ! $ast instanceof Namespace_) {
				continue;
			}

			$ast->stmts = array_filter(
				$ast->stmts, function ($stmt) {
				return $stmt instanceof Function_;
			}
			);
		}

		return $asts;
	}

	/**
	 * Removes any class statement from the list of statements.
	 *
	 * @param array $asts
	 *
	 * @return array
	 */
	protected function removeClasses(array $asts) {
		return array_filter(
			$asts, function (NodeAbstract $stmt) {
			return ! $stmt instanceof Class_;
		}
		);
	}

	/**
	 * Removes any non-function statement from the list of statements.
	 *
	 * @param array $asts
	 *
	 * @return array
	 */
	protected function removeNonFunctions(array $asts) {
		return array_filter(
			$asts, function (NodeAbstract $stmt) {
			return $stmt instanceof Function_ || $stmt instanceof Namespace_;
		}
		);
	}

	/**
	 * Removes the functions excluded in the configuration file from the list of
	 * functions.
	 *
	 * @param array $asts
	 *
	 * @return array
	 */
	protected function removeExcludedFunctions(array $asts) {
		return array_filter(
			$asts, function (NodeAbstract $stmt) {
			if ($stmt instanceof Function_) {
				return ! in_array(
					$stmt->name, $this->config['exclude-functions'], true
				);
			} elseif ($stmt instanceof Namespace_) {
				$stmt->stmts = $this->removeExcludedFunctions($stmt->stmts);

				return true;
			}
		}
		);
	}

	/**
	 * Parses the list of functions found in the source to set what will be
	 * returned by the generated class `defined` method.
	 *
	 * @param array $asts
	 */
	protected function setDefinedFunctions($asts) {
		$definedHere = array_reduce(
			$asts, function ($carry, Stmt $stmt) {
			if ($stmt instanceof Function_) {
				$carry[] = new String_($stmt->name);
			} elseif ($stmt instanceof Namespace_) {
				$carry = array_merge(
					$carry, array_map(
					function (Function_ $function) use ($stmt) {
						return new String_(
							'\\' . $stmt->name . '\\' . $function->name
						);
					}, $stmt->stmts
				)
				);
			}

			return $carry;
		}, []
		);

		$this->defined = array_merge($this->defined, $definedHere);
	}

	/**
	 * @param $source
	 *
	 * @return array
	 */
	protected function getSourceAstsFromFolder($source) {
		$files = [];
		$dirs  = [$source];
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

		$files = $this->filterIncludedExcludedFiles($files);

		$asts = array_map(
			function ($file) {
				return $this->getFileFunctionsAsts($file);
			}, $files
		);

		$merged = array_merge(...$asts);

		$this->definedCount = count($merged);

		return $merged;
	}

	/**
	 * @param $files
	 */
	protected function filterIncludedExcludedFiles($files) {
		$excludedPaths = $this->normalizePaths($this->config['exclude-paths']);
		$includedPaths = $this->normalizePaths($this->config['include-paths']);

		$afterExclusion = array_filter(
			$files, function ($file) use ($excludedPaths, $includedPaths) {
			$isUnderIncludedPath = count(
				array_filter(
					$includedPaths, function ($inc) use ($file) {
					return 0 !== strpos($file, $inc);
				}
				)
			);

			if ($isUnderIncludedPath) {
				return true;
			}

			$isUnderExcludedPath = count(
				array_filter(
					$excludedPaths, function ($ex) use ($file) {
					return 0 !== strpos($file, $ex);
				}
				)
			);

			return ! $isUnderExcludedPath;
		}
		);

		return $afterExclusion;
	}

	/**
	 * @param $paths
	 *
	 * @return array
	 */
	protected function normalizePaths($paths) {
		if (empty($paths)) {
			return [];
		}

		$paths = array_unique(
			array_merge($paths), array_map(
			function ($ex) {
				return realpath($ex);
			}, $paths
		)
		);

		return $paths;
	}

	/**
	 * Prints the class PHP code to file.
	 *
	 * @param string $envName
	 * @param string $envPath
	 * @param array  $functionsAsts
	 *
	 * @return int
	 */
	protected function printEnvironment($envName, $envPath, array $functionsAsts
	) {
		$environmentClassFrags   = explode('\\', $envName);
		$environmentClass        = array_pop($environmentClassFrags);
		$outputFilePath          = rtrim($envPath, DIRECTORY_SEPARATOR)
			. "/{$environmentClass}.php";
		$functionsFileName       = "{$environmentClass}-functions.php";
		$outputFunctionsFilePath = rtrim($envPath, DIRECTORY_SEPARATOR) . '/'
			. $functionsFileName;
		$realpath                = realpath($outputFilePath) ?: $outputFilePath;
		$functionsFileRealpath   = realpath($outputFunctionsFilePath)
			?: $outputFunctionsFilePath;

		$asts = [];

		$builder   = new BuilderFactory();
		$date      = date('Y-m-d H:i:s');
		$docLine
		           = "/*\n* Auto-generated by the function-mocker-le package with the `fmle generate:environment command`\n* on {$date}\n*/";
		$classStmt = $builder->class($environmentClass)->implement(
				'\\' . Environment::class
			)->setDocComment($docLine)->addStmt(
				$builder->method('name')->makePublic()->addStmt(
						new Return_(new String_($environmentClass))
					)
			)->addStmt(
				$builder->method('setUp')->makePublic()->addParam(
						new Param('args', null, null, false, true)
					)->addStmt(
						new Include_(
							new Concat(
								new Scalar\MagicConst\Dir(),
								new String_('/' . $functionsFileName)
							), Include_::TYPE_INCLUDE_ONCE
						)
					)
			)->addStmt(
				$builder->method('tearDown')->makePublic()->addStmt(
						new FuncCall(
							new Name('\\tad\\FunctionMockerLe\\undefineAll'), [
								new MethodCall(
									new Variable('this'), new Name('defined')
								),
							]
						)
					)
			)->addStmt(
				$builder->method('defined')->makePublic()->addStmt(
						new Return_(new Array_($this->defined))
					)
			);

		$environmentNamespace = $this->getEnvironmentNamespace($envName);

		if ( ! empty($environmentNamespace)) {
			$asts[] = $builder->namespace($environmentNamespace)->addStmt(
				$classStmt
			)->getNode();
		} else {
			$asts[] = $classStmt->getNode();
		}

		$namespacedStmts              = array_filter(
			$functionsAsts, function (Stmt $stmt) {
			return $stmt instanceof Namespace_;
		}
		);
		$globalNamespaceFunctionsStmt = new Namespace_(
			null, array_filter(
			$functionsAsts, function (Stmt $stmt) {
			return $stmt instanceof Function_;
		}
		)
		);

		array_unshift($namespacedStmts, $globalNamespaceFunctionsStmt);
		$wroteEnvFunctionsFile = $this->writeToFile(
			$namespacedStmts, $functionsFileRealpath
		);
		$wroteEnvClassFile     = $this->writeToFile($asts, $realpath);

		return $wroteEnvFunctionsFile && $wroteEnvClassFile;
	}

	/**
	 * @param $environmentClassFrags
	 *
	 * @return string
	 */
	protected function getEnvironmentNamespace($envName) {
		$envClassFrags = explode('\\', $envName);
		array_pop($envClassFrags);

		return implode('\\', $envClassFrags);
	}

	protected function writeToFile(array $asts, $realpath, $fileDoc = '') {
		$output = (new PrettyPrinter())->prettyPrintFile($asts);
		$this->output->writeln(
			"<comment>Writing to file {$realpath}</comment>"
		);

		if ( ! is_dir(dirname($realpath))
			&& ! mkdir(
				dirname($realpath), 0777, true
			)
			&& ! is_dir(dirname($realpath))
		) {
			throw new \RuntimeException(
				sprintf(
					'Directory "%s" could not be created', dirname($realpath)
				)
			);
		}

		if ( ! empty($fileDoc)) {
			$output = $fileDoc . "\n\n" . $output;
		}
		$put = file_put_contents($realpath, $output, LOCK_EX);

		if ($put) {
			$this->output->writeln(
				"<info>Done! Check out the {$realpath} file.</info>"
			);

			return 0;
		}

		$this->output->writeln(
			"<error>Could not write to {$realpath}...</error>"
		);

		return 1;
	}

	/**
	 * Wraps all the functions statements found in the source in
	 * `if(function_exists...` checks.
	 *
	 * @param array $asts
	 *
	 * @return array
	 */
	protected function wrapInFunctionExistsCheck($asts) {
		return array_map(
			function (Function_ $functionNode) {
				return new If_(
					new BooleanNot(
						new FuncCall(
							new Name('function_exists'),
							[new Arg(new Scalar\String_($functionNode->name))]
						)
					), [
					'stmts' => [
						new FuncCall(
							new Name('\tad\FunctionMockerLe\define'), [
							new String_($functionNode->name), new Closure(
								[
									'params' => $functionNode->params,
									'stmts'  => [
										new Return_(),
									],
								]
							),
						]
						),
					],
				]
				);
			}, $asts
		);
	}

	/**
	 * Configures the command.
	 */
	protected function configure() {
		$this->setName(self::NAME)->setDescription(
				'Scaffolds a Environment from source files'
			)->setHelp(
				'This command will parse a specified folder, or file, and generate a Environment defining the functions found in the source.'
			)->addArgument(
				'source', InputArgument::REQUIRED,
				'The source file or folder path, relative to the current working directory.'
			)->addArgument(
				'env', InputArgument::REQUIRED,
				'The name, including namespace, of the Environment class to generate'
			)->addOption(
				'env-path', 'sp', InputOption::VALUE_OPTIONAL,
				'The path to the folder that will contain the Environment file; if the destination does not exist it will be created.',
				getcwd()
			)->addOption(
				'config-file', 'c', InputOption::VALUE_OPTIONAL,
				'The path to a generation configuration file'
			);
	}
}
