<?php
class View {
	private $pageVars = array();
	private $template;
    private $config;
	public function __construct()
	{
        global $config;
        $this->config=$config;
	}

	public function set($var, $val)
	{
		$this->pageVars[$var] = $val;
	}

	public function render($template)
	{
        $tpl_cache=ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.$this->config['theme'].DIRECTORY_SEPARATOR.$template.'.php';
        $tpl=APP_DIR.'views'.DIRECTORY_SEPARATOR . $this->config['theme'].DIRECTORY_SEPARATOR . strtolower($template).'.php';
        if(!file_exists($tpl_cache)){
            if(!is_dir(ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.$this->config['theme'])){
                mkdir(ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'tpl',0755);
                mkdir(ROOT_DIR.'runtime'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.$this->config['theme'],0755);
            }
            copy($tpl,$tpl_cache);
            $match=true;
            while($match){
                $tpl_content=file_get_contents($tpl_cache);
                preg_match_all('/(include|require|include_once|require_once)\((\"|\')(.*?)(\"|\')\)(\s*)\;/',$tpl_content,$matches);
                $pattern=array();
                $replace=array();
                if(count($matches[0])>0){
                    foreach($matches[0] as $k=>$v){
                        $pattern[]='/\<\?php(\s*)'.preg_quote($v).'(\s*)\?\>/';
                        $replace[]=file_get_contents(APP_DIR .'views'.DIRECTORY_SEPARATOR.$this->config['theme'].DIRECTORY_SEPARATOR.$matches[3][$k]);
                    }
                    $tpl_temp=preg_replace($pattern,$replace,$tpl_content);
                    file_put_contents($tpl_cache,$tpl_temp);
                }else{
                    $match=false;
                }
            }
        }
		extract($this->pageVars);
		ob_start();
        if(CACHE_ON){
            include($tpl_cache);
        }else{
            include($tpl);
        }
		echo ob_get_clean();
	}
    
}

?>