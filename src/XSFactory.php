<?php

    declare(strict_types = 1);

    namespace Coco\xunsearch;

    use Coco\xunsearch\exception\XSErrorException;

class XSFactory extends XS
{
    /**
     * @var XS[] $xs
     */
    private static array $xs     = [];
    private static bool  $inited = false;

    /**
     * @param array $arr
     *
     * @return XS
     * @throws XSErrorException|exception\XSException
     */
    public static function getIns(array $arr): XS
    {
        $hash = md5(json_encode($arr));

        if (!isset(static::$xs[$hash])) {
            static::$xs[$hash] = new static($arr);
        }

        static::init();

        return static::$xs[$hash];
    }

    /**
     * @throws XSErrorException
     */
    private static function init(): void
    {
        if (!static::$inited) {
            static::initConst();
            static::initErrorHandler();

            static::$inited = true;
        }
    }

    /**
     * @return void
     * @throws XSErrorException
     */
    private static function initErrorHandler(): void
    {
        set_error_handler(function ($errno, $error, $file, $line): bool {
            if (($errno & ini_get('error_reporting')) && !strncmp($file, XS_LIB_ROOT, strlen(XS_LIB_ROOT))) {
                throw new XSErrorException($errno, $error, $file, $line);
            }

            return false;
        });
    }

    /**
     * @return void
     */
    private static function initConst(): void
    {
        defined('XS_LIB_ROOT') or define('XS_LIB_ROOT', dirname(__FILE__));
        defined('XS_CMD_NONE') or define('XS_CMD_NONE', 0);
        defined('XS_CMD_DEFAULT') or define('XS_CMD_DEFAULT', XS_CMD_NONE);
        defined('XS_CMD_PROTOCOL') or define('XS_CMD_PROTOCOL', 20110707);
        defined('XS_CMD_USE') or define('XS_CMD_USE', 1);
        defined('XS_CMD_HELLO') or define('XS_CMD_HELLO', 1);
        defined('XS_CMD_DEBUG') or define('XS_CMD_DEBUG', 2);
        defined('XS_CMD_TIMEOUT') or define('XS_CMD_TIMEOUT', 3);
        defined('XS_CMD_QUIT') or define('XS_CMD_QUIT', 4);
        defined('XS_CMD_INDEX_SET_DB') or define('XS_CMD_INDEX_SET_DB', 32);
        defined('XS_CMD_INDEX_GET_DB') or define('XS_CMD_INDEX_GET_DB', 33);
        defined('XS_CMD_INDEX_SUBMIT') or define('XS_CMD_INDEX_SUBMIT', 34);
        defined('XS_CMD_INDEX_REMOVE') or define('XS_CMD_INDEX_REMOVE', 35);
        defined('XS_CMD_INDEX_EXDATA') or define('XS_CMD_INDEX_EXDATA', 36);
        defined('XS_CMD_INDEX_CLEAN_DB') or define('XS_CMD_INDEX_CLEAN_DB', 37);
        defined('XS_CMD_DELETE_PROJECT') or define('XS_CMD_DELETE_PROJECT', 38);
        defined('XS_CMD_INDEX_COMMIT') or define('XS_CMD_INDEX_COMMIT', 39);
        defined('XS_CMD_INDEX_REBUILD') or define('XS_CMD_INDEX_REBUILD', 40);
        defined('XS_CMD_FLUSH_LOGGING') or define('XS_CMD_FLUSH_LOGGING', 41);
        defined('XS_CMD_INDEX_SYNONYMS') or define('XS_CMD_INDEX_SYNONYMS', 42);
        defined('XS_CMD_INDEX_USER_DICT') or define('XS_CMD_INDEX_USER_DICT', 43);
        defined('XS_CMD_SEARCH_DB_TOTAL') or define('XS_CMD_SEARCH_DB_TOTAL', 64);
        defined('XS_CMD_SEARCH_GET_TOTAL') or define('XS_CMD_SEARCH_GET_TOTAL', 65);
        defined('XS_CMD_SEARCH_GET_RESULT') or define('XS_CMD_SEARCH_GET_RESULT', 66);
        defined('XS_CMD_SEARCH_SET_DB') or define('XS_CMD_SEARCH_SET_DB', XS_CMD_INDEX_SET_DB);
        defined('XS_CMD_SEARCH_GET_DB') or define('XS_CMD_SEARCH_GET_DB', XS_CMD_INDEX_GET_DB);
        defined('XS_CMD_SEARCH_ADD_DB') or define('XS_CMD_SEARCH_ADD_DB', 68);
        defined('XS_CMD_SEARCH_FINISH') or define('XS_CMD_SEARCH_FINISH', 69);
        defined('XS_CMD_SEARCH_DRAW_TPOOL') or define('XS_CMD_SEARCH_DRAW_TPOOL', 70);
        defined('XS_CMD_SEARCH_ADD_LOG') or define('XS_CMD_SEARCH_ADD_LOG', 71);
        defined('XS_CMD_SEARCH_GET_SYNONYMS') or define('XS_CMD_SEARCH_GET_SYNONYMS', 72);
        defined('XS_CMD_SEARCH_SCWS_GET') or define('XS_CMD_SEARCH_SCWS_GET', 73);
        defined('XS_CMD_QUERY_GET_STRING') or define('XS_CMD_QUERY_GET_STRING', 96);
        defined('XS_CMD_QUERY_GET_TERMS') or define('XS_CMD_QUERY_GET_TERMS', 97);
        defined('XS_CMD_QUERY_GET_CORRECTED') or define('XS_CMD_QUERY_GET_CORRECTED', 98);
        defined('XS_CMD_QUERY_GET_EXPANDED') or define('XS_CMD_QUERY_GET_EXPANDED', 99);
        defined('XS_CMD_OK') or define('XS_CMD_OK', 128);
        defined('XS_CMD_ERR') or define('XS_CMD_ERR', 129);
        defined('XS_CMD_SEARCH_RESULT_DOC') or define('XS_CMD_SEARCH_RESULT_DOC', 140);
        defined('XS_CMD_SEARCH_RESULT_FIELD') or define('XS_CMD_SEARCH_RESULT_FIELD', 141);
        defined('XS_CMD_SEARCH_RESULT_FACETS') or define('XS_CMD_SEARCH_RESULT_FACETS', 142);
        defined('XS_CMD_SEARCH_RESULT_MATCHED') or define('XS_CMD_SEARCH_RESULT_MATCHED', 143);
        defined('XS_CMD_DOC_TERM') or define('XS_CMD_DOC_TERM', 160);
        defined('XS_CMD_DOC_VALUE') or define('XS_CMD_DOC_VALUE', 161);
        defined('XS_CMD_DOC_INDEX') or define('XS_CMD_DOC_INDEX', 162);
        defined('XS_CMD_INDEX_REQUEST') or define('XS_CMD_INDEX_REQUEST', 163);
        defined('XS_CMD_IMPORT_HEADER') or define('XS_CMD_IMPORT_HEADER', 191);
        defined('XS_CMD_SEARCH_SET_SORT') or define('XS_CMD_SEARCH_SET_SORT', 192);
        defined('XS_CMD_SEARCH_SET_CUT') or define('XS_CMD_SEARCH_SET_CUT', 193);
        defined('XS_CMD_SEARCH_SET_NUMERIC') or define('XS_CMD_SEARCH_SET_NUMERIC', 194);
        defined('XS_CMD_SEARCH_SET_COLLAPSE') or define('XS_CMD_SEARCH_SET_COLLAPSE', 195);
        defined('XS_CMD_SEARCH_KEEPALIVE') or define('XS_CMD_SEARCH_KEEPALIVE', 196);
        defined('XS_CMD_SEARCH_SET_FACETS') or define('XS_CMD_SEARCH_SET_FACETS', 197);
        defined('XS_CMD_SEARCH_SCWS_SET') or define('XS_CMD_SEARCH_SCWS_SET', 198);
        defined('XS_CMD_SEARCH_SET_CUTOFF') or define('XS_CMD_SEARCH_SET_CUTOFF', 199);
        defined('XS_CMD_SEARCH_SET_MISC') or define('XS_CMD_SEARCH_SET_MISC', 200);
        defined('XS_CMD_QUERY_INIT') or define('XS_CMD_QUERY_INIT', 224);
        defined('XS_CMD_QUERY_PARSE') or define('XS_CMD_QUERY_PARSE', 225);
        defined('XS_CMD_QUERY_TERM') or define('XS_CMD_QUERY_TERM', 226);
        defined('XS_CMD_QUERY_TERMS') or define('XS_CMD_QUERY_TERMS', 232);
        defined('XS_CMD_QUERY_RANGEPROC') or define('XS_CMD_QUERY_RANGEPROC', 227);
        defined('XS_CMD_QUERY_RANGE') or define('XS_CMD_QUERY_RANGE', 228);
        defined('XS_CMD_QUERY_VALCMP') or define('XS_CMD_QUERY_VALCMP', 229);
        defined('XS_CMD_QUERY_PREFIX') or define('XS_CMD_QUERY_PREFIX', 230);
        defined('XS_CMD_QUERY_PARSEFLAG') or define('XS_CMD_QUERY_PARSEFLAG', 231);
        defined('XS_CMD_SORT_TYPE_RELEVANCE') or define('XS_CMD_SORT_TYPE_RELEVANCE', 0);
        defined('XS_CMD_SORT_TYPE_DOCID') or define('XS_CMD_SORT_TYPE_DOCID', 1);
        defined('XS_CMD_SORT_TYPE_VALUE') or define('XS_CMD_SORT_TYPE_VALUE', 2);
        defined('XS_CMD_SORT_TYPE_MULTI') or define('XS_CMD_SORT_TYPE_MULTI', 3);
        defined('XS_CMD_SORT_TYPE_GEODIST') or define('XS_CMD_SORT_TYPE_GEODIST', 4);
        defined('XS_CMD_SORT_TYPE_MASK') or define('XS_CMD_SORT_TYPE_MASK', 0x3f);
        defined('XS_CMD_SORT_FLAG_RELEVANCE') or define('XS_CMD_SORT_FLAG_RELEVANCE', 0x40);
        defined('XS_CMD_SORT_FLAG_ASCENDING') or define('XS_CMD_SORT_FLAG_ASCENDING', 0x80);
        defined('XS_CMD_QUERY_OP_AND') or define('XS_CMD_QUERY_OP_AND', 0);
        defined('XS_CMD_QUERY_OP_OR') or define('XS_CMD_QUERY_OP_OR', 1);
        defined('XS_CMD_QUERY_OP_AND_NOT') or define('XS_CMD_QUERY_OP_AND_NOT', 2);
        defined('XS_CMD_QUERY_OP_XOR') or define('XS_CMD_QUERY_OP_XOR', 3);
        defined('XS_CMD_QUERY_OP_AND_MAYBE') or define('XS_CMD_QUERY_OP_AND_MAYBE', 4);
        defined('XS_CMD_QUERY_OP_FILTER') or define('XS_CMD_QUERY_OP_FILTER', 5);
        defined('XS_CMD_RANGE_PROC_STRING') or define('XS_CMD_RANGE_PROC_STRING', 0);
        defined('XS_CMD_RANGE_PROC_DATE') or define('XS_CMD_RANGE_PROC_DATE', 1);
        defined('XS_CMD_RANGE_PROC_NUMBER') or define('XS_CMD_RANGE_PROC_NUMBER', 2);
        defined('XS_CMD_VALCMP_LE') or define('XS_CMD_VALCMP_LE', 0);
        defined('XS_CMD_VALCMP_GE') or define('XS_CMD_VALCMP_GE', 1);
        defined('XS_CMD_PARSE_FLAG_BOOLEAN') or define('XS_CMD_PARSE_FLAG_BOOLEAN', 1);
        defined('XS_CMD_PARSE_FLAG_PHRASE') or define('XS_CMD_PARSE_FLAG_PHRASE', 2);
        defined('XS_CMD_PARSE_FLAG_LOVEHATE') or define('XS_CMD_PARSE_FLAG_LOVEHATE', 4);
        defined('XS_CMD_PARSE_FLAG_BOOLEAN_ANY_CASE') or define('XS_CMD_PARSE_FLAG_BOOLEAN_ANY_CASE', 8);
        defined('XS_CMD_PARSE_FLAG_WILDCARD') or define('XS_CMD_PARSE_FLAG_WILDCARD', 16);
        defined('XS_CMD_PARSE_FLAG_PURE_NOT') or define('XS_CMD_PARSE_FLAG_PURE_NOT', 32);
        defined('XS_CMD_PARSE_FLAG_PARTIAL') or define('XS_CMD_PARSE_FLAG_PARTIAL', 64);
        defined('XS_CMD_PARSE_FLAG_SPELLING_CORRECTION') or define('XS_CMD_PARSE_FLAG_SPELLING_CORRECTION', 128);
        defined('XS_CMD_PARSE_FLAG_SYNONYM') or define('XS_CMD_PARSE_FLAG_SYNONYM', 256);
        defined('XS_CMD_PARSE_FLAG_AUTO_SYNONYMS') or define('XS_CMD_PARSE_FLAG_AUTO_SYNONYMS', 512);
        defined('XS_CMD_PARSE_FLAG_AUTO_MULTIWORD_SYNONYMS') or define('XS_CMD_PARSE_FLAG_AUTO_MULTIWORD_SYNONYMS', 1536);
        defined('XS_CMD_PREFIX_NORMAL') or define('XS_CMD_PREFIX_NORMAL', 0);
        defined('XS_CMD_PREFIX_BOOLEAN') or define('XS_CMD_PREFIX_BOOLEAN', 1);
        defined('XS_CMD_INDEX_WEIGHT_MASK') or define('XS_CMD_INDEX_WEIGHT_MASK', 0x3f);
        defined('XS_CMD_INDEX_FLAG_WITHPOS') or define('XS_CMD_INDEX_FLAG_WITHPOS', 0x40);
        defined('XS_CMD_INDEX_FLAG_SAVEVALUE') or define('XS_CMD_INDEX_FLAG_SAVEVALUE', 0x80);
        defined('XS_CMD_INDEX_FLAG_CHECKSTEM') or define('XS_CMD_INDEX_FLAG_CHECKSTEM', 0x80);
        defined('XS_CMD_VALUE_FLAG_NUMERIC') or define('XS_CMD_VALUE_FLAG_NUMERIC', 0x80);
        defined('XS_CMD_INDEX_REQUEST_ADD') or define('XS_CMD_INDEX_REQUEST_ADD', 0);
        defined('XS_CMD_INDEX_REQUEST_UPDATE') or define('XS_CMD_INDEX_REQUEST_UPDATE', 1);
        defined('XS_CMD_INDEX_SYNONYMS_ADD') or define('XS_CMD_INDEX_SYNONYMS_ADD', 0);
        defined('XS_CMD_INDEX_SYNONYMS_DEL') or define('XS_CMD_INDEX_SYNONYMS_DEL', 1);
        defined('XS_CMD_SEARCH_MISC_SYN_SCALE') or define('XS_CMD_SEARCH_MISC_SYN_SCALE', 1);
        defined('XS_CMD_SEARCH_MISC_MATCHED_TERM') or define('XS_CMD_SEARCH_MISC_MATCHED_TERM', 2);
        defined('XS_CMD_SEARCH_MISC_WEIGHT_SCHEME') or define('XS_CMD_SEARCH_MISC_WEIGHT_SCHEME', 3);
        defined('XS_CMD_SCWS_GET_VERSION') or define('XS_CMD_SCWS_GET_VERSION', 1);
        defined('XS_CMD_SCWS_GET_RESULT') or define('XS_CMD_SCWS_GET_RESULT', 2);
        defined('XS_CMD_SCWS_GET_TOPS') or define('XS_CMD_SCWS_GET_TOPS', 3);
        defined('XS_CMD_SCWS_HAS_WORD') or define('XS_CMD_SCWS_HAS_WORD', 4);
        defined('XS_CMD_SCWS_GET_MULTI') or define('XS_CMD_SCWS_GET_MULTI', 5);
        defined('XS_CMD_SCWS_SET_IGNORE') or define('XS_CMD_SCWS_SET_IGNORE', 50);
        defined('XS_CMD_SCWS_SET_MULTI') or define('XS_CMD_SCWS_SET_MULTI', 51);
        defined('XS_CMD_SCWS_SET_DUALITY') or define('XS_CMD_SCWS_SET_DUALITY', 52);
        defined('XS_CMD_SCWS_SET_DICT') or define('XS_CMD_SCWS_SET_DICT', 53);
        defined('XS_CMD_SCWS_ADD_DICT') or define('XS_CMD_SCWS_ADD_DICT', 54);
        defined('XS_CMD_ERR_UNKNOWN') or define('XS_CMD_ERR_UNKNOWN', 600);
        defined('XS_CMD_ERR_NOPROJECT') or define('XS_CMD_ERR_NOPROJECT', 401);
        defined('XS_CMD_ERR_TOOLONG') or define('XS_CMD_ERR_TOOLONG', 402);
        defined('XS_CMD_ERR_INVALIDCHAR') or define('XS_CMD_ERR_INVALIDCHAR', 403);
        defined('XS_CMD_ERR_EMPTY') or define('XS_CMD_ERR_EMPTY', 404);
        defined('XS_CMD_ERR_NOACTION') or define('XS_CMD_ERR_NOACTION', 405);
        defined('XS_CMD_ERR_RUNNING') or define('XS_CMD_ERR_RUNNING', 406);
        defined('XS_CMD_ERR_REBUILDING') or define('XS_CMD_ERR_REBUILDING', 407);
        defined('XS_CMD_ERR_WRONGPLACE') or define('XS_CMD_ERR_WRONGPLACE', 450);
        defined('XS_CMD_ERR_WRONGFORMAT') or define('XS_CMD_ERR_WRONGFORMAT', 451);
        defined('XS_CMD_ERR_EMPTYQUERY') or define('XS_CMD_ERR_EMPTYQUERY', 452);
        defined('XS_CMD_ERR_TIMEOUT') or define('XS_CMD_ERR_TIMEOUT', 501);
        defined('XS_CMD_ERR_IOERR') or define('XS_CMD_ERR_IOERR', 502);
        defined('XS_CMD_ERR_NOMEM') or define('XS_CMD_ERR_NOMEM', 503);
        defined('XS_CMD_ERR_BUSY') or define('XS_CMD_ERR_BUSY', 504);
        defined('XS_CMD_ERR_UNIMP') or define('XS_CMD_ERR_UNIMP', 505);
        defined('XS_CMD_ERR_NODB') or define('XS_CMD_ERR_NODB', 506);
        defined('XS_CMD_ERR_DBLOCKED') or define('XS_CMD_ERR_DBLOCKED', 507);
        defined('XS_CMD_ERR_CREATE_HOME') or define('XS_CMD_ERR_CREATE_HOME', 508);
        defined('XS_CMD_ERR_INVALID_HOME') or define('XS_CMD_ERR_INVALID_HOME', 509);
        defined('XS_CMD_ERR_REMOVE_HOME') or define('XS_CMD_ERR_REMOVE_HOME', 510);
        defined('XS_CMD_ERR_REMOVE_DB') or define('XS_CMD_ERR_REMOVE_DB', 511);
        defined('XS_CMD_ERR_STAT') or define('XS_CMD_ERR_STAT', 512);
        defined('XS_CMD_ERR_OPEN_FILE') or define('XS_CMD_ERR_OPEN_FILE', 513);
        defined('XS_CMD_ERR_TASK_CANCELED') or define('XS_CMD_ERR_TASK_CANCELED', 514);
        defined('XS_CMD_ERR_XAPIAN') or define('XS_CMD_ERR_XAPIAN', 515);
        defined('XS_CMD_OK_INFO') or define('XS_CMD_OK_INFO', 200);
        defined('XS_CMD_OK_PROJECT') or define('XS_CMD_OK_PROJECT', 201);
        defined('XS_CMD_OK_QUERY_STRING') or define('XS_CMD_OK_QUERY_STRING', 202);
        defined('XS_CMD_OK_DB_TOTAL') or define('XS_CMD_OK_DB_TOTAL', 203);
        defined('XS_CMD_OK_QUERY_TERMS') or define('XS_CMD_OK_QUERY_TERMS', 204);
        defined('XS_CMD_OK_QUERY_CORRECTED') or define('XS_CMD_OK_QUERY_CORRECTED', 205);
        defined('XS_CMD_OK_SEARCH_TOTAL') or define('XS_CMD_OK_SEARCH_TOTAL', 206);
        defined('XS_CMD_OK_RESULT_BEGIN') or define('XS_CMD_OK_RESULT_BEGIN', XS_CMD_OK_SEARCH_TOTAL);
        defined('XS_CMD_OK_RESULT_END') or define('XS_CMD_OK_RESULT_END', 207);
        defined('XS_CMD_OK_TIMEOUT_SET') or define('XS_CMD_OK_TIMEOUT_SET', 208);
        defined('XS_CMD_OK_FINISHED') or define('XS_CMD_OK_FINISHED', 209);
        defined('XS_CMD_OK_LOGGED') or define('XS_CMD_OK_LOGGED', 210);
        defined('XS_CMD_OK_RQST_FINISHED') or define('XS_CMD_OK_RQST_FINISHED', 250);
        defined('XS_CMD_OK_DB_CHANGED') or define('XS_CMD_OK_DB_CHANGED', 251);
        defined('XS_CMD_OK_DB_INFO') or define('XS_CMD_OK_DB_INFO', 252);
        defined('XS_CMD_OK_DB_CLEAN') or define('XS_CMD_OK_DB_CLEAN', 253);
        defined('XS_CMD_OK_PROJECT_ADD') or define('XS_CMD_OK_PROJECT_ADD', 254);
        defined('XS_CMD_OK_PROJECT_DEL') or define('XS_CMD_OK_PROJECT_DEL', 255);
        defined('XS_CMD_OK_DB_COMMITED') or define('XS_CMD_OK_DB_COMMITED', 256);
        defined('XS_CMD_OK_DB_REBUILD') or define('XS_CMD_OK_DB_REBUILD', 257);
        defined('XS_CMD_OK_LOG_FLUSHED') or define('XS_CMD_OK_LOG_FLUSHED', 258);
        defined('XS_CMD_OK_DICT_SAVED') or define('XS_CMD_OK_DICT_SAVED', 259);
        defined('XS_CMD_OK_RESULT_SYNONYMS') or define('XS_CMD_OK_RESULT_SYNONYMS', 280);
        defined('XS_CMD_OK_SCWS_RESULT') or define('XS_CMD_OK_SCWS_RESULT', 290);
        defined('XS_CMD_OK_SCWS_TOPS') or define('XS_CMD_OK_SCWS_TOPS', 291);
        defined('XS_PACKAGE_BUGREPORT') or define('XS_PACKAGE_BUGREPORT', "http://www.xunsearch.com/bugs");
        defined('XS_PACKAGE_NAME') or define('XS_PACKAGE_NAME', "xunsearch");
        defined('XS_PACKAGE_TARNAME') or define('XS_PACKAGE_TARNAME', "xunsearch");
        defined('XS_PACKAGE_URL') or define('XS_PACKAGE_URL', "");
        defined('XS_PACKAGE_VERSION') or define('XS_PACKAGE_VERSION', "1.4.17");
    }
}
