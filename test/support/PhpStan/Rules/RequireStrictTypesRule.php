<?php

declare(strict_types=1);

namespace Test\Support\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\InlineHTML;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\IdentifierRuleError;

/**
 * @implements Rule<Node>
 */
final class RequireStrictTypesRule implements Rule {

    public function getNodeType(): string {
        return Node::class;
    }

    /**
     * @param Node $node
     * @param Scope $scope
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array {
        // Only run once per file
        if (!$node instanceof Node\Stmt\Namespace_
            && !$node instanceof Node\Stmt\Class_
            && !$node instanceof InlineHTML) {
            return [];
        }

        $file = $scope->getFile();

        static $checkedFiles = [];

        if (isset($checkedFiles[$file])) {
            return [];
        }

        $checkedFiles[$file] = true;

        $contents = file_get_contents($file);

        if ($contents === false) {
            return [];
        }

        if (preg_match('/declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $contents)) {
            return [];
        }

        $error_msg = sprintf('File "%s" must declare strict types: declare(strict_types=1);', $file);
        return [
            RuleErrorBuilder::message($error_msg)
		    ->identifier('myCustomRules.declareStrictTypes')
		    ->build()
        ];
    }

}
