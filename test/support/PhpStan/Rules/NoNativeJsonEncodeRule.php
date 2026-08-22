<?php

declare(strict_types=1);

namespace Test\Support\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<FuncCall>
 */
final class NoNativeJsonEncodeRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Name) {
            return [];
        }

        if ($node->name->toString() !== 'json_encode') {
            return [];
        }

        $error_msg = 'Do not call json_encode() directly. Use JsonEncode::class wrapper instead.';
        return [
            RuleErrorBuilder::message($error_msg)
                ->identifier('customRules.noNativeJsonEncodeRule')
                ->build(),
        ];
    }
}
