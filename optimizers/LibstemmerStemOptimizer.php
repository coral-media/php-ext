<?php

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Compiler\CompilerException;
use Zephir\Optimizers\OptimizerAbstract;

class LibstemmerStemOptimizer extends OptimizerAbstract
{
    /**
     * @param array $expression
     * @param Call $call
     * @param CompilationContext $context
     * @return CompiledExpression|bool
     * @throws CompilerException
     */
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (!isset($expression['parameters'])) {
            throw new CompilerException("'libstemmer_stem' requires parameters", $expression);
        }

        if (count($expression['parameters']) !== 2) {
            throw new CompilerException("'libstemmer_stem' requires exactly two parameters", $expression);
        }

        $context->headersManager->add('snowball_bridge');

        /**
         * Resolve the parameters to C variables.
         * getReadOnlyResolvedParams is efficient as it doesn't separate the zvals 
         * unless necessary.
         */
        $resolvedParams = $call->getReadOnlyResolvedParams($expression['parameters'], $context, $expression);

        /**
         * We expect a zend_string* (word) and a char* (lang).
         * Zephir variables in generated C are usually zval pointers. 
         * We need to extract the string values using Zend macros.
         */
        $word = "Z_STR_P(" . $resolvedParams[0] . ")";
        $lang = "Z_STRVAL_P(" . $resolvedParams[1] . ")";

        /**
         * Create a temporary variable to hold the returned zend_string* from your bridge
         */
        $symbol = $context->symbolTable->getTempVariableForWrite('string', $context, $expression);
        
        // Output the actual C call to the generated file
        // We use ZVAL_STR to wrap the zend_string* returned by your bridge into the Zephir variable
        $context->codePrinter->output("ZVAL_STR(&" . $symbol->getName() . ", libstemmer_stem(" . $word . ", " . $lang . "));");

        return new CompiledExpression('variable', $symbol->getName(), $expression);
    }
}