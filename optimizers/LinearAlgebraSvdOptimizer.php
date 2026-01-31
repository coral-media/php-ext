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
         * params:
         * 0 = x
         * 1 = rows
         * 2 = cols
         * 3 = jobz (string, we take first char)
         */
        $context->codePrinter->output(
            sprintf(
                "
                char jobz = Z_STRVAL_P(%s)[0];
                linear_algebra_svd_zval(
                    %s,
                    zephir_get_intval(%s),
                    zephir_get_intval(%s),
                    jobz,
                    &%s
                );
                ",
                $params[3],
                $params[0],
                $params[1],
                $params[2],
                $symbol->getName()
            )
        );

        return new CompiledExpression('variable', $symbol->getName(), $expression);
    }
}