<?php

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Exception\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class LinearAlgebraVectorNormalizeOptimizer extends OptimizerAbstract
{
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters']) || count($expression['parameters']) !== 2) {
            throw new CompilerException(
                "'linear_algebra_vector_normalize' requires exactly 2 parameters (x, method)",
                $expression
            );
        }

        $params = $call->getReadOnlyResolvedParams(
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
                "linear_algebra_vector_normalize_zval(%s, zephir_get_intval(%s), &%s);",
                $params[0],
                $params[1],
                $symbol->getName()
            )
        );

        return new CompiledExpression('variable', $symbol->getName(), $expression);
    }
}