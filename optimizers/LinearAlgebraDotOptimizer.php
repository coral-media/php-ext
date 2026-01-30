<?php

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Compiler\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class LinearAlgebraDotOptimizer extends OptimizerAbstract
{
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters']) || count($expression['parameters']) !== 2) {
            throw new CompilerException("'ccontrol_dot' requires exactly two parameters", $expression);
        }

        $resolvedParams = $call->getReadOnlyResolvedParams(
            $expression['parameters'],
            $context,
            $expression
        );

        $symbol = $context->symbolTable->getTempVariableForWrite(
            'variable',
            $context,
            $expression
        );

        $context->headersManager->add('ccontrol_bridge');

        $context->codePrinter->output(
            "ZVAL_DOUBLE(&{$symbol->getName()}, linear_algebra_dot({$resolvedParams[0]}, {$resolvedParams[1]}));"
        );

        return new CompiledExpression('variable', $symbol->getName(), $expression);
    }
}