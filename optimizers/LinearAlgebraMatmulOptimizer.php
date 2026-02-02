<?php

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Exception\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class LinearAlgebraMatmulOptimizer extends OptimizerAbstract
{
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters']) || count($expression['parameters']) < 5) {
            throw new CompilerException(
                "'linear_algebra_matmul' requires at least 5 parameters (a, b, m, n, k)",
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

        /**
         * ABI (matches lapack_bridge.c):
         * void linear_algebra_matmul_zval(
         *     zval *a, zval *b,
         *     int m, int n, int k,
         *     zend_bool transpose_a, zend_bool transpose_b,
         *     zval *return_value
         * );
         *
         * params:
         * 0 = a (zval* - array)
         * 1 = b (zval* - array)
         * 2 = m (int - rows in A)
         * 3 = n (int - cols in A / rows in B)
         * 4 = k (int - cols in B)
         * 5 = transpose_a (bool - optional, default false)
         * 6 = transpose_b (bool - optional, default false)
         */
        
        $transpose_a = $params[5] ?? '0';
        $transpose_b = $params[6] ?? '0';

        $context->codePrinter->output(
            sprintf(
                "linear_algebra_matmul_zval(%s, %s, zephir_get_intval(%s), zephir_get_intval(%s), zephir_get_intval(%s), zephir_get_boolval(%s), zephir_get_boolval(%s), &%s);",
                $params[0],
                $params[1],
                $params[2],
                $params[3],
                $params[4],
                $transpose_a,
                $transpose_b,
                $symbol->getName()
            )
        );

        return new CompiledExpression('variable', $symbol->getName(), $expression);
    }
}