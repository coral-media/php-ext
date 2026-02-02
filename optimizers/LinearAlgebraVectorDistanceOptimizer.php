<?php
namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Exception\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class LinearAlgebraVectorDistanceOptimizer extends OptimizerAbstract
{
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (count($expression['parameters']) < 2) {
            throw new CompilerException(
                "'linear_algebra_vector_distance' requires at least 2 parameters",
                $expression
            );
        }

        $params = $call->getReadOnlyResolvedParams(
            $expression['parameters'],
            $context,
            $expression
        );

        $symbol = $context->symbolTable->getTempVariableForWrite(
            'double',
            $context,
            $expression
        );

        $context->headersManager->add('lapack_bridge');

        $method = $params[2] ?? '1';
        $p      = $params[3] ?? '3.0';

        $context->codePrinter->output(
            sprintf(
                "linear_algebra_vector_distance_zval(%s, %s, zephir_get_intval(%s), zephir_get_doubleval(%s), &%s);",
                $params[0],
                $params[1],
                $method,
                $p,
                $symbol->getName()
            )
        );

        return new CompiledExpression('double', $symbol->getName(), $expression);
    }
}