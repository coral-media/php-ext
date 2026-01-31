<?php

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Compiler\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class LinearAlgebraNormOptimizer extends OptimizerAbstract
{
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters']) || count($expression['parameters']) !== 2) {
            throw new CompilerException(
                "'linear_algebra_norm' requires exactly 2 parameters (x, method)",
                $expression
            );
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

        $context->headersManager->add('lapack_bridge');

        $context->codePrinter->output(
            sprintf(
                "ZVAL_DOUBLE(&%s, linear_algebra_norm_zval(%s, zephir_get_intval(%s)));",
                $symbol->getName(),
                $resolvedParams[0],
                $resolvedParams[1]
            )
        );

        return new CompiledExpression('variable', $symbol->getName(), $expression);
    }
}