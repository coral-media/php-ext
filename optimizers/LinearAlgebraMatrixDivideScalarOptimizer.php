<?php

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Exception\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class LinearAlgebraMatrixDivideScalarOptimizer extends OptimizerAbstract
{
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters']) || count($expression['parameters']) !== 4) {
            throw new CompilerException(
                "'linear_algebra_matrix_divide_scalar' requires 4 parameters (a, scalar, rows, cols)",
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
                "linear_algebra_matrix_divide_scalar_zval(%s, zephir_get_doubleval(%s), zephir_get_intval(%s), zephir_get_intval(%s), &%s);",
                $params[0],
                $params[1],
                $params[2],
                $params[3],
                $symbol->getName()
            )
        );

        return new CompiledExpression('variable', $symbol->getName(), $expression);
    }
}
