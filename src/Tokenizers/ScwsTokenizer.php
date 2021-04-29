<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch\Tokenizers;

use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\AbstractTokenizer;
use JimChen\LaravelScout\XunSearch\Tokenizers\Results\Top;
use SplFixedArray;
use XS;
use XSCommand;
use XSDocument;
use const XS_CMD_OK_SCWS_RESULT;
use const XS_CMD_OK_SCWS_TOPS;
use const XS_CMD_SCWS_GET_RESULT;
use const XS_CMD_SCWS_GET_TOPS;
use const XS_CMD_SCWS_SET_IGNORE;
use const XS_CMD_SEARCH_SCWS_GET;
use const XS_CMD_SEARCH_SCWS_SET;

class ScwsTokenizer extends AbstractTokenizer
{
    protected $server;

    protected $charset;

    protected $setting;

    /**
     * @return void
     */
    protected function init()
    {
        parent::init();
        $this->server = $this->xs->getScwsServer();
        $this->charset = $this->xs->getDefaultCharset();
    }

    /**
     * @param string          $value
     * @param XSDocument|null $doc
     * @return SplFixedArray
     */
    public function getTokens($value, XSDocument $doc = null): SplFixedArray
    {
        $tokens = [];
        $this->setIgnore(true);
        // save charset, force to use UTF-8
        $_charset = $this->charset;
        $this->charset = 'UTF-8';
        $words = $this->getResult($value);
        foreach ($words as $word) {
            $tokens[] = $word['word'];
        }
        // restore charset
        $this->charset = $_charset;
        return SplFixedArray::fromArray($tokens);
    }

    /**
     * @param string $text
     * @param int    $limit
     * @param string $xattr
     * @return SplFixedArray
     * @throws \XSException
     */
    public function getTops($text, $limit = 10, $xattr = ''): SplFixedArray
    {
        $words = [];
        $text = $this->applySetting($text);
        $cmd = new XSCommand(XS_CMD_SEARCH_SCWS_GET, XS_CMD_SCWS_GET_TOPS, $limit, $text, $xattr);
        $res = $this->server->execCommand($cmd, XS_CMD_OK_SCWS_TOPS);
        while ($res->buf !== '') {
            $tmp = unpack('Itimes/a4attr/a*word', $res->buf);
            $tmp['word'] = XS::convert($tmp['word'], $this->charset, 'UTF-8');
            $words[] = new Top($tmp['times'], $tmp['attr'], $tmp['word']);
            $res = $this->server->getRespond();
        }
        return SplFixedArray::fromArray($words);
    }

    /**
     * 获取分词结果
     * @param string $text 待分词的文本
     * @return array 返回词汇数组, 每个词汇是包含 [off:词在文本中的位置,attr:词性,word:词]
     */
    public function getResult($text)
    {
        $words = [];
        $text = $this->applySetting($text);
        $cmd = new XSCommand(XS_CMD_SEARCH_SCWS_GET, XS_CMD_SCWS_GET_RESULT, 0, $text);
        $res = $this->server->execCommand($cmd, XS_CMD_OK_SCWS_RESULT);
        while ($res->buf !== '') {
            $tmp = unpack('Ioff/a4attr/a*word', $res->buf);
            $tmp['word'] = XS::convert($tmp['word'], $this->charset, 'UTF-8');
            $words[] = $tmp;
            $res = $this->server->getRespond();
        }
        return $words;
    }

    /**
     * 设置忽略标点符号
     * @param bool $yes 是否忽略
     * @return self 返回对象本身以支持串接操作
     */
    public function setIgnore($yes = true)
    {
        $this->setting['ignore'] = new XSCommand(XS_CMD_SEARCH_SCWS_SET, XS_CMD_SCWS_SET_IGNORE, $yes === false ? 0 : 1);
        return $this;
    }

    /**
     * @param string $text
     * @return string
     * @throws \XSException
     */
    public function applySetting($text)
    {
        $this->xs->getScwsServer()->reopen();
        foreach ($this->setting as $key => $cmd) {
            if (is_array($cmd)) {
                foreach ($cmd as $_cmd) {
                    $this->server->execCommand($_cmd);
                }
            } else {
                $this->server->execCommand($cmd);
            }
        }
        return XS::convert($text, 'UTF-8', $this->charset);
    }
}
