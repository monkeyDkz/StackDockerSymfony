<?php

namespace App\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\TokenType;
use Doctrine\ORM\Query\AST;

class JsonContainsFunction extends FunctionNode
{
    /**
     * @var AST\AggregateExpression | AST\InputParameter | mixed
     */
    private $expr1;

    /**
     * @var AST\AggregateExpression | AST\InputParameter | mixed
     */
    private $expr2;

    public function parse(\Doctrine\ORM\Query\Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // (2)
        $parser->match(TokenType::T_OPEN_PARENTHESIS); // (3)
        $this->expr1 = $parser->ArithmeticPrimary(); // (4)
        $parser->match(TokenType::T_COMMA); // (5)
        $this->expr2 = $parser->ArithmeticPrimary(); // (6)
        $parser->match(TokenType::T_CLOSE_PARENTHESIS); // (3)
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker): string
    {
        return sprintf(
            '(%s::TEXT = ANY(SELECT json_array_elements_text(%s)))',
            $this->expr2->dispatch($sqlWalker),
            $this->expr1->dispatch($sqlWalker),
        );
    }
}