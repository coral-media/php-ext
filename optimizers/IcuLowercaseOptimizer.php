<?php

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Exception\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class IcuLowercaseOptimizer extends OptimizerAbstract
{
    /**
     * @param array $expression
     * @param Call $call
     * @param CompilationContext $context
     * @return CompiledExpression
     * @throws CompilerException
     */
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters']) || count($expression['parameters']) !== 2) {
            throw new CompilerException(
                "'icu_lowercase' requires exactly 2 parameters (text, locale)",
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

        // Add the ICU bridge header
        $context->headersManager->add('icu_bridge');

        // Generate C code: icu_lowercase(Z_STR_P(text), Z_STRVAL_P(locale), &return_value)
        $context->codePrinter->output(
            sprintf(
                "icu_lowercase(Z_STR_P(%s), Z_STRVAL_P(%s), &%s);",
                $params[0],
                $params[1],
                $symbol->getName()
            )
        );

        return new CompiledExpression('variable', $symbol->getName(), $expression);
    }
}
