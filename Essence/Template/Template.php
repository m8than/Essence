<?php
namespace Essence\Template;

class Template
{
    private $view_file;
    private $view_data;

    private $temp_dir;
    private $view_dir;
    public function __construct($filename, $view_data)
    {
        $this->view_dir = essence('app_dir') . '/' . essence('views_dir') . '/';
        $this->temp_dir =  essence('app_dir') . '/' . essence('temp_dir') . '/tpl_cache/';

        $this->view_file = $filename;
        $this->view_data = $view_data;
        $this->view_data['this'] = $this;
    }

    private function dynamicReplacements($data)
    {
        //non php functions or overrides
        $toreplace = array();
        preg_match_all('/<\?php(.*?)\?>|<\?(.*?)\?>|{#(.*?)#}/m', $data, $dynamic_parts);
        foreach ($dynamic_parts[3] as $dynamic_part) {
            preg_match_all('/(include|require) (\'|")(.*?)(\'|");/', $dynamic_part, $includes);
            for ($i = 0;$i < count($includes[0]); $i++) {
                $src = file_get_contents(essence('app_dir') . '/' . essence('views_dir') . '/' .  $includes[3][$i]);                
                $toreplace[$includes[0][$i]] = $includes[1][$i] . ' ' . '$this->getCache("'.$includes[3][$i].'");';                
            }

            preg_match_all('/\$([a-zA-Z0-9]*)/', $dynamic_part, $vars);
            for ($i = 0; $i < count($vars[0]); $i++) {
                $toreplace[$vars[0][$i]] = "\$this->view_data['".$vars[1][$i]."']";
            }
        }
        return strtr($data, $toreplace);
    }

    private function replacements($data)
    {
        $data = preg_replace("/{#\s*(if\s*\(.*\))\s*#}/i", '{#$1:#}', $data);
        $data = preg_replace("/{#\s*\/if\s*#}/i", '{#endif;#}', $data);
        
        $data = preg_replace("/{#\s*(elseif\s*\(.*\))\s*#}/i", '{#$1:#}', $data);
        $data = preg_replace("/{#\s*else\s*#}/i", '{#else:#}', $data);
        
        $data = preg_replace("/{#\s*(foreach\s*\(.*\))\s*#}/i", '{#$1:#}', $data);
        $data = preg_replace("/{#\s*\/foreach\s*#}/i", '{#endforeach;#}', $data);
        
        $data = preg_replace("/{#\s*(switch\s*\(.*\))\s*#}/i", '{#$1:#}', $data);
        $data = preg_replace("/{#\s*\/switch\s*#}/i", '{#endswitch;#}', $data);
        
        $data = preg_replace("/{#\s*(case\s*.*)\s*#}/i", '{#$1:#}', $data);
        $data = preg_replace("/{#\s*\/case\s*#}/i", '{#break;#}', $data);
        
        $data = preg_replace("/{#\s*(for\s*\(.*\))\s*#}/i", '{#$1:#}', $data);
        $data = preg_replace("/{#\s*\/for\s*#}/i", '{#endfor;#}', $data);
        
        $data = preg_replace("/{#\s*(while\s*\(.*\))\s*#}/i", '{#$1:#}', $data);
        $data = preg_replace("/{#\s*\/while\s*#}/i", '{#endwhile;#}', $data);
                
        $data = preg_replace('/{#\s*(\$[a-zA-Z\[\]\'\_\$\-\>]*)\s*#}/i', '{#=$1#}', $data);
        
        $data = str_replace("{#=", "<?=", $data);
        $data = str_replace("{#", "<?php ", $data);
        $data = str_replace("#}", "?>", $data);
                
        return $data;
    }

    private function getCache($view_file)
    {
        $cache_path = $this->temp_dir . $this->_getTempFileName($view_file);
    
        $write_cache = true;
        if(is_file($cache_path))
        {
            $mtime_cache = filemtime($cache_path);
            $mtime_view = filemtime($this->view_dir . $view_file);
            
            if($mtime_view <= $mtime_cache)
            {
                $write_cache = false;
            }
        }
        
        if($write_cache)
        {
            $finalTemplateData = file_get_contents($this->view_dir . $view_file);
            $finalTemplateData = $this->dynamicReplacements($finalTemplateData);
            $finalTemplateData = $this->replacements($finalTemplateData);
            $finalTemplateData = '<?php if (!defined(\'ESSENCE_SECURE\')) { die(); } ?>'.$finalTemplateData;
            if (!is_dir(dirname($cache_path)))
            {
                mkdir(dirname($cache_path), 777, true);
            }
            file_put_contents($cache_path, $finalTemplateData);
        }
        
        return $cache_path;
    }

    public function output()
    {
        ob_start();
        require $this->getCache($this->view_file);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    private function _getTempFileName($path)
    {
        return md5($path . essence('app_key'));
    }
}