<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Tokenizers\Contracts;

use JimChen\LaravelScout\XunSearch\Tokenizers\Results\Top;
use SplFixedArray;
use XSDocument;

interface TokenizerContract
{
    /**
     * 获取重要词统计结果
     * @param string $text 待分词的文本
     * @param string $xattr 在返回结果的词性过滤, 多个词性之间用逗号分隔, 以~开头取反
     *  如: 设为 n,v 表示只返回名词和动词; 设为 ~n,v 则表示返回名词和动词以外的其它词
     * @return SplFixedArray<Top> 返回词汇数组, 每个词汇是包含 [times:次数,attr:词性,word:词]
     */
    public function getTops($text, $limit = 10, $xattr = ''): SplFixedArray;

    /**
     * 执行分词并返回词列表
     * @param string $value 待分词的字段值(UTF-8编码)
     * @param XSDocument $doc 当前相关的索引文档
     * @return SplFixedArray 切好的词组成的数组
     */
    public function getTokens($value, XSDocument $doc = null): SplFixedArray;
}
