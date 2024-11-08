<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$finder = Finder::create()
  ->in(__DIR__ . '/src')
  ->exclude('vendor');

$config = new Config();
return $config->setRules([
  '@PSR12' => true,
  'array_syntax' => ['syntax' => 'short'],
  'ordered_imports' => true,
  'no_unused_imports' => true,
  'single_quote' => true,
  'trailing_comma_in_multiline' => true,
])
  ->setFinder($finder);
