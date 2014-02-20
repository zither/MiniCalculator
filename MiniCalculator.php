<?php
/**
 * @file MiniCalculator.php
 * @brief 一个非常简单采用前缀表达式的计算解释器
 * @author JonChou <ilorn.mc@gmail.com>
 * @version 0.1
 * @date 2014-02-19
 */

class MiniCalculator {
    
    /**
     * 存储未解析的计算式
     */
    public $codes;

    /**
     * 计算式分解后的 Tokens
     */
    public $tokens;

    /**
     * 解析后的语法树
     */
    public $syntaxTree;

    /**
     * 解释器入口
     *
     * @param $codes
     */
    public function interpret($codes)
    {
        $this->codes = $codes;
        $this->tokens = $this->tokenize($codes);
        $this->syntaxTree = $this->buildSyntaxTree();
        printf("Your expression is %s.\n", $this->codes);
        printf("The answer is %s.", $this->recursiveCalcNodes($this->syntaxTree));
    }

    /**
     * 词法分析
     *
     * @param $codes
     *
     * @return array
     */
    public function tokenize($codes)
    {

        $codes = trim(str_replace(['(', ')'], [' ( ', ' ) '], $codes));
        // 直接使用数组作为 token 栈，方便使用数组函数
        return array_filter(array_reverse(explode(' ' , $codes)), 'strlen');
    }
    
    /**
     * 生成方便数组操作的语法树
     *
     * @return array
     */
    public function buildSyntaxTree()
    {
        return array_reverse($this->parser());
    }

    /**
     * 解析 Tokens 生成语法树
     *
     * @param $ast
     *
     * @return array
     */
    public function parser($ast = null)
    {
        if (empty($this->tokens)) {
            return $ast;
        }
        // 语法树为空时直接新建一个语法树
        if (is_null($ast)) {
            $ast = [];
        }
        $token = array_pop($this->tokens);
        if ($token === ')') {
            return $ast;
        } elseif ($token === '(') {
            array_push($ast, $this->parser());
            return $this->parser($ast);
        } else {
            array_push($ast, $this->categorize($token));
            return $this->parser($ast);
        }
    }

    /**
     * 标识符分类
     *
     * @param $token
     *
     * @return array
     */
    public function categorize($token)
    {
        if (is_numeric($token)) {
            return ['type' => 'number', 'value' => $token];
        } else {
            return ['type' => 'identifier', 'value' => $token];
        }
    }
    
    /**
     * 递归计算语法树的每个节点
     *
     * @param $node
     *
     * @return number
     */
    public function recursiveCalcNodes($node)
    {
        $op = $numbers = [];
        $result = 0;
        foreach ($node as $argv) {
            if (isset($argv['type'])) {
                if ($argv['type'] === 'identifier') {
                    $op[] = $argv['value'];
                } else {
                    $numbers[] = $argv['value'];
                }
            } else {
                $numbers[] = $result = $this->recursiveCalcNodes($argv);
            }
        }
        return empty($op) ? $result : $this->calculate($op, $numbers);
    }
    
    /**
     * 将操作符运用到指定的数字中，返回计算结果
     *
     * @param $op 
     * @param $numbers
     *
     * @return number
     */
    public function calculate($op, $numbers)
    {
        // 每次计算只接受一个操作符，多余的会被忽略
        $op = array_shift($op);
        $result = 0;
        if ($op === '-' && count($numbers) < 2) {
            $result -= $numbers[0];
        } elseif (in_array($op, array('+', '-', '*', '/'))){
            $phpCode = sprintf('$result = array_shift($numbers);
                                foreach ($numbers as $number) {
                                    $result = $result %s $number;
                                }', $op);
            eval($phpCode);
        } else {
            throw new Exception('Operator Undefined.');
        }

        return $result;
    }
}

// S-expression 示例
$expression = '(+ (- 8 3) 8 (* 2 7))'; //output : 27
$interp = new MiniCalculator();
$interp->interpret($expression);
