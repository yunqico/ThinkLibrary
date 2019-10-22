<?php

// +----------------------------------------------------------------------
// | Library for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2019 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://demo.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 仓库地址 ：https://gitee.com/zoujingli/ThinkLibrary
// | github 仓库地址 ：https://github.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------

namespace think\admin;

use think\admin\storage\LocalStorage;
use think\admin\storage\QiniuStorage;

/**
 * 文件存储引擎管理
 * Class Storage
 * @package library
 * @see LocalStorage
 */
class Storage
{
    /**
     * 存储域名前缀
     * @var string
     */
    protected $prefix;

    /**
     * 存储对象缓存
     * @var array
     */
    protected static $object = [];

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function __call($method, $args)
    {
        $class = self::instance(sysconf('storage_type'));
        if (method_exists($class, $method)) return call_user_func_array([$class, $method], $args);
        throw new \think\Exception("method not exists: " . get_class($class) . "->{$method}()");
    }

    /**
     * 设置文件驱动名称
     * @param string $name
     * @return LocalStorage|QiniuStorage
     * @throws \think\Exception
     */
    public static function instance($name)
    {
        if (isset(self::$object[$class = ucfirst(strtolower($name))])) {
            return self::$object[$class];
        }
        if (class_exists($object = __NAMESPACE__ . "\\storage\\{$class}Storage")) {
            return self::$object[$class] = new $object;
        }
        throw new \think\Exception("File driver [{$class}] does not exist.");
    }

    /**
     * 获取文件相对名称
     * @param string $url 文件访问链接
     * @param string $ext 文件后缀名称
     * @param string $pre 文件存储前缀
     * @param string $fun 名称规则方法
     * @return string
     */
    public static function name($url, $ext = '', $pre = '', $fun = 'md5')
    {
        empty($ext) && $ext = pathinfo($url, 4);
        empty($ext) || $ext = trim($ext, '.\\/');
        empty($pre) || $pre = trim($pre, '.\\/');
        $splits = array_merge([$pre], str_split($fun($url), 16));
        return trim(join('/', $splits), '/') . '.' . strtolower($ext ? $ext : 'tmp');
    }

    /**
     * 根据文件后缀获取文件MINE
     * @param array $exts 文件后缀
     * @param array $mime 文件MINE信息
     * @return string
     */
    public static function mime($exts, $mime = [])
    {
        $mimes = self::mimes();
        foreach (is_string($exts) ? explode(',', $exts) : $exts as $e) {
            $mime[] = isset($mimes[strtolower($e)]) ? $mimes[strtolower($e)] : 'application/octet-stream';
        }
        return join(',', array_unique($mime));
    }

    /**
     * 获取所有文件扩展的MINES
     * @return array
     */
    public static function mimes()
    {
        static $mimes = [];
        if (count($mimes) > 0) return $mimes;
        return $mimes = include __DIR__ . '/storage/bin/mimes.php';
    }

}