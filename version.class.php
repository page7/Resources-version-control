<?php
/**
 * resources' version
 +-----------------------------------------
 * @category    tool
 * @package     tool
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace tool;

class version
{
    // specify resource's path by file extension
    static protected $paths = array(
        'png'   => RESOURCES_URL . 'img/',
        'gif'   => RESOURCES_URL . 'img/',
        'jpg'   => RESOURCES_URL . 'img/',
        'jpeg'  => RESOURCES_URL . 'img/',
        'js'    => RESOURCES_URL . 'js/',
        'css'   => RESOURCES_URL . 'css/',
        '*'     => RESOURCES_URL . 'file/',
    );


    // Cache options
    //    file:$path
    //    db:{$confname:}$table
    static protected $cache = 'file:/log/res_version.php';



    // Refresh file version
    static public function refresh($filename=null, $path=null)
    {
        $ext = substr($filename, strrpos($filename, '.')+1);
        $path = ($path !== null ? $path : self::$paths[$ext]) . $filename;
        $true_path = $path[0] == '/' ? PT_PATH . $path : $path;

        $content = @file_get_contents($true_path);
        preg_match('/\/\*.*?v([0-9.]+).*?\*\//is', $content, $v);

        if (!$v || !$v[1])
            $version = substr(md5($content), 0, 8);
        else
            $version = $v[1];

        if ($content)
            self::cache($path, $version);

        return $version;
    }
    // refresh



    // Refresh All
    static public function refresh_all()
    {
        $all = cache();
        $report = array();
        foreach ($all as $file => $ver)
        {
            $new = self::refresh($file, '');
            $report = array('file'=>$file, 'oldver'=>$ver, 'newver'=>$new)
        }
        return $report;
    }
    // refresh all




    // Load resource's version
    static public function load($filename, $path=null, $return=false)
    {
        $ext = substr($filename, strrpos($filename, '.')+1);
        $path = ($path !== null ? $path : self::$paths[$ext]) . $filename;
        $version = self::cache($path);

        if ($return)
            return $path . '?v=' . $version;
        else
            echo $path . '?v=' . $version;
    }
    // load




    // Cache version data
    static private function cache($path=null, $version=null)
    {
        static $cache = array();

        $config = explode(':', self::$cache);
        $type = array_shift($config);

        // Load version
        if ($version === null)
        {
            if (!$cache)
            {
                switch ($type)
                {
                    case 'file':
                        $cache = file_exists(PT_PATH . $config[0]) ? include PT_PATH . $config[0] : array();
                        break;

                    case 'db':
                        $db_conf = count($config) > 1 ? \config($config[0]) : null;
                        $db_table = count($config) > 1 ? $config[1] : $config[0];
                        $db = \pt\framework\db::init($db_conf);
                        $data = $db -> prepare("SELECT `version`, `file` FROM `{$db_table}`") -> execute();
                        $cache = array_column($data, 'version', 'file');
                        break;

                }
            }

            if (!$path)
                return $cache;

            if (isset($cache[$path]))
                return $cache[$path];

            return self::refresh($path, '');
        }
        // Save version
        else
        {
            $cache[$path] = $version;

            switch ($type)
            {
                case 'file':
                    $cache_file = PT_PATH . $config[0];
                    return file_put_contents($cache_file, '<?php return '.var_export($cache, true).';');
                    break;

                case 'db':
                    $db_conf = count($config) > 1 ? \config($config[0]) : null;
                    $db_table = count($config) > 1 ? $config[1] : $config[0];
                    $db = \pt\framework\db::init($db_conf);
                    $rs = $db -> prepare("REPLACE INTO `{$db_table}` (`version`, `file`) VALUES (:ver, :file);") -> execute(array(':ver'=>$version, ':file'=>$path));
                    return $rs === false ? false : true;
                    break;
            }
        }
    }
    // cache

}
