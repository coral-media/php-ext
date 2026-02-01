<?php

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Compiler\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class LinearAlgebraVectorNormalizeOptimizer extends OptimizerAbstract
{
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters']) || count($expression['parameters']) !== 1) {
            throw new CompilerException(
                "'linear_algebra_vector_normalize' requires exactly 1 parameter (x)",
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

        // ABI:
        // void linear_algebra_vector_normalize_zval(zval *x, zval *return_value);
        $context->codePrinter->output(
            sprintf(
                "linear_algebra_vector_normalize_zval(%s, &%s);",
                $params[0],
                $symbol->getName()
            )
        );

        return new CompiledExpression('variable', $symbol->getName(), $expression);
    }
}
