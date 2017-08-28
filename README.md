# Function Mocker, Light Edition

When [function-mocker](https://github.com/lucatume/function-mocker) is overkill.

## Code example
```php
<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;
use function tad\FunctionMockerLe\define;
use function tad\FunctionMockerLe\defineWithMap;
use function tad\FunctionMockerLe\defineWithValueMap;
use function tad\FunctionMockerLe\defineAll;

class MyOrmTest extends TestCase {

	public function test_query_parameters_are_preserved(){
		// define a non existing function
		define('get_post', function(array $args){
			Assert::assertEquals(['post_type' => 'book','paginated' => 2, 'posts_per_page' => 6], $args);
			
			return [];
		});
		
		$orm = new MyOrm('book');
		
		$orm->query()
			->page(2)
			->perPage(6)
			->fetch();
	}
	
	public function test_users_cannot_fetch_posts_they_cannot_read(){
		// define a number of functions using a <function> => <callback> map
		defineWithMap([
			'is_user_logged_in' =>  function(){
				return true;
			},
			'get_posts' => function(){
				return[
					['ID' => 1, 'post_title' => 'One'],
					['ID' => 2, 'post_title' => 'Two'],
					['ID' => 3, 'post_title' => 'Three'],
					['ID' => 4, 'post_title' => 'Four']
				];
			},
		'current_user_can' => function($cap, $id){
				// a post ID to 'can' map
				$map  = [
					1 => true,
					2 => true,
					3 => false,
					4 => true
				];
				
				return $map($id);
				}
		]);
		
		$orm = new MyOrm('book');
		
		$books = $orm->query()
			->fetch();
		
		$expected = [
				['ID' => 1, 'post_title' => 'One'],
				['ID' => 2, 'post_title' => 'Two'],
				['ID' => 4, 'post_title' => 'Four']
		];
		
		$this->assertEquals($expected, $books);
	}
	
	public function test_sticky_posts_are_not_prepended_if_not_home_archive_or_tag(){
		// define a number of functions all with the same callback...
		defineAll(['is_home', 'is_archive', 'is_tag'], function(){
			return false;
		});
		define('get_posts', function(array $args){
			Assert::arrayHasKey('ignore_sticky_posts,', $args);
			Assert::assertEquals('1', $args['ignore_sticky_posts']);
		});
		
		$orm = new MyOrm('book');
		$orm->query()->fetch();
		
		// ...and then redefine them
		defineWithValueMap([
			'is_home' => true,
			'is_archive' => false,
			'is_tag' => false
		]);
		
		// redefine as needed
		define('get_posts', function(array $args){
			Assert::arrayNotHasKey('ignore_sticky_posts,', $args);
		});
		
		$orm->query()->fetch();
	}
}
```

## Installation
Use [Composer](https://getcomposer.org/) to require the library in the project
```bash
composer require --dev lucatume/function-mocker-le
```

## Why mocking functions? What problem does this solve?
The library provides a lightweight and dependency-free function mocking solution.  
The reason one might want to mock a function in the tests is to test any code that depends on a framework based on functions (e.g. WordPress).  
The difference between this library and [function-mocker](https://github.com/lucatume/function-mocker) is that this will only work when none of the function it defines are defined during the execution; function-mocker will allow instead monkey-patching the functions at runtime even if those are already defined.  
Where function mocker depends on the [Patchwork library](https://github.com/antecedent/patchwork) to allow for user-land monkey-patching this library has a minimal code footprint and only provides a handful of functions.  
Patchwork or function-mocker should be used if **really** loading the mocked functions source cannot be avoided in the tests.  

## Usage
The library core function is the `define($function, $callback)` one: it defines a function and sets its content to a callback; in its basic usage it allows setting up a mocked function return value in the tests:

```php
public function test_current_user_can(){
	tad\FunctionMockerLe\define('current_user_can', function(){
		return true;
	});
	
	$this->assertTrue(current_user_can('read_posts'));
}
```

The `$callback` argument can be a `callable` of any kind, in this example I'm using the [Prophecy mocking engine](https://github.com/phpspec/prophecy) to use set complex expectations:

```php
public function test_prophecy(){
	$pr = $this->prophesize(User::class);
	$pr->can('read_posts')->shouldBeCalled();
	$pr->can('edit_posts')->shouldNotBeCalled();
	
	tad\FunctionMockerLe\define('current_user_can', [$pr->reveal(), 'can']);
	
	current_user_can('read_posts');
	current_user_can('edit_posts'); // this will trigger a failure: not expected
	current_user_can('read_posts');
}
```

Relying on basic assertions provided by hte [PhpUnit](https://phpunit.de/ "PHPUnit – The PHP Testing Framework") library is another option:

```php
public function test_prophecy(){
	tad\FunctionMockerLe\define('current_user_can', function($cap){
		PHPUnit\Framework\Assert::assertNotEquals('edit_posts', $cap);
	});
	
	current_user_can('read_posts');
	current_user_can('edit_posts'); // this will trigger a failure: not expected
	current_user_can('read_posts');
}
```

Where [function-mocker](https://github.com/lucatume/function-mocker) tries to provide a feature-reach solution to the problem of mocking functions (and more); this project tries to provide just a basic starting point with no opinionated choices about its usage.
