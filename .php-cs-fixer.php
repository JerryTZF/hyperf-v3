<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
$header = <<<'EOF'
This file is part of Hyperf.

@link     https://www.hyperf.io
@document https://hyperf.wiki
@contact  group@hyperf.io
@license  https://github.com/hyperf/hyperf/blob/master/LICENSE
EOF;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        '@DoctrineAnnotation' => true,
        '@PhpCsFixer' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'separate' => 'none',
            'location' => 'after_declare_strict',
        ],
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'list_syntax' => [
            'syntax' => 'short',
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => null,
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'declare',
            ],
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class', 'function', 'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'yoda_style' => [
            'always_move_variable' => false,
            'equal' => false,
            'identical' => false,
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'multiline_whitespace_before_semicolons' => [ // 分号紧跟结束语句
            'strategy' => 'no_multi_line',
        ],
        'constant_case' => [ // 关键字小写
            'case' => 'lower',
        ],
        'class_attributes_separation' => true, // 关联数组注释推荐
        'combine_consecutive_unsets' => true, // 合并unset
        'declare_strict_types' => true, // 严格模式
        'linebreak_after_opening_tag' => true, // 关键字后换行
        'lowercase_static_reference' => true, // 静态调用小写化
        'no_useless_else' => true, // 移除非必要的else
        'no_unused_imports' => true, // 移除无用的引用
        'not_operator_with_successor_space' => true, // 取反(!)增加右空格
        'not_operator_with_space' => false, // 取反(!)增加左右空格
        'ordered_class_elements' => true, // 对类/接口/特征/枚举的元素进行排序。
        'php_unit_strict' => false, // PHPUnit 方法（如 assertSame）应取代 assertEquals。
        'phpdoc_separation' => false, // PHPDoc 中的注释应该组合在一起，以便相同类型的注释紧跟在一起，并且不同类型的注释由单个空行分隔
        'single_quote' => true, // 单引号替代双引号
        'standardize_not_equals' => true, // Replace all <> with !=
        'multiline_comment_opening_closing' => false, // 必须以两个星号开头，多行注释必须以单个星号开头，在开头的斜线后面
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('public')
            ->exclude('runtime')
            ->exclude('vendor')
            ->in(__DIR__)
    )
    ->setUsingCache(false);
