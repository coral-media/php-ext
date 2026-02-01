<?php

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Compiler\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class LinearAlgebraSvdOptimizer extends OptimizerAbstract
{
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters']) || count($expression['parameters']) !== 4) {
            throw new CompilerException(
                "'linear_algebra_svd' requires (x, rows, cols, jobz)",
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
         * void linear_algebra_svd_zval(zval *x, int rows, int cols, zval *jobz_zv, zval *return_value);
         *
         * params:
         * 0 = x (zval*)
         * 1 = rows (zval*)
         * 2 = cols (zval*)
         * 3 = jobz (zval* string: "N"|"S"|"A")
         */
        $context->codePrinter->output(
            sprintf(
                "linear_algebra_svd_zval(%s, zephir_get_intval(%s), zephir_get_intval(%s), %s, &%s);",
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