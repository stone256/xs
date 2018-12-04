<?php
class examples_model {
	var $path;

	function __construct(){
		$this->path = _X_MODULE.'/examples/view/examples/content';
	}


	/**
	 * return mane
	 */
	function get_menu(){
/** test ** /
		$arr=array(
			array('name'=>array('pa'=>array('ims'=>33))),
			array('id'=>8),
		);
		_dv(xpAS::grab($arr,'ims'));
/**/
		$menu = $this->_get_menu($this->path);
		return $menu;
	}


	/**
	 * return menu struct
	 */
	function _get_menu($dir){

		$dirs = scandir($dir);
		 sort($dirs, SORT_NATURAL);
		 foreach ($dirs as $k=>$v){
		 	if(in_array($v, array('.', '..', 'index.html'))) continue;
		 	$name = ucwords(str_replace('-', ' ', $v));
		 	$link = null;
		 	if(is_dir($dir.'/'.$v)){ //this is folder
		 		$type = 'folder';
		 		$link = (array)$this->_get_menu($dir.'/'.$v);
		 		$content = $this->_get_child_link($link);
		 		if(file_exists($dir.'/'.$v.'/index.html')){
			 		foreach ((array)$this->_get_bookmark($dir.'/'.$v.'/index.html') as $l){
			 			$link1[] = array('name'=>$l, 'type'=>'file', 'bookmark'=>1, 'content'=>$dir.'/'.$v.'/index.html');
			 		}
			 		$content = $dir.'/'.$v.'/index.html';
			 		while(count((array)$link1)){
			 			array_unshift($link, array_pop($link1));
			 		}
		 		}

		 	}else{ //is file
		 		$type = 'file';
		 		$content = $dir.'/'.$v;
		 		foreach ((array)$this->_get_bookmark($content) as $l){
		 			$link[] = array('name'=>$l, 'type'=>'file', 'bookmark'=>1, 'content'=>$content);
		 		}

		 	}
		 	$menu[] = array('name'=>$name, 'link'=>$link, 'content'=>$content, 'type'=>$type);

		 }
		return $menu;
	}

	/**
	 * when parent menu item does not have content to link to , it will use it's child one.
	 */
	function _get_child_link($children){
		foreach ((array) $children as $child){
			if($child['content']) return $child['content'];
			if($child['link']) return $this->_get_child_link($child['link']);
		}
	}

	/**
	 * get bookmark in a html file;
	 */
	function _get_bookmark($f){
		$con = file_get_contents($f);
		preg_match_all('/<a\s+name\=\"(.*?)\"\s*\>.*?\<\/a\>/ims', $con, $tmp);
		return count($tmp[1]) ? $tmp[1] : null;
	}

	/**
	 * return html content
	 */
	function rendering($menu){
		$con .= '<ul class="">';
		$in = $_REQUEST['in'];
		foreach ($menu as $m){
			$path = str_replace($this->path, '', $m['content']);
			$t = $m['type'] == 'file' ? 'doc' : $m['type'];
			$active = !$m['bookmark'] && $path == $in ? 'active' : '';
			$open = $active ? "open" : "";
			$con .= '<li class="examples_menu '.$t.' '.$open.'" >';
			if($m['content']) $con .= '<a class="'.$active.'"  href="?in='.$path.($m['bookmark']? '#'.$m['name'] : '').'">';
				$con .= $m['type'] == 'file' ? preg_replace('/\.[^\.]{1,4}$/', '', $m['name']): $m['name'];
			if($m['content'])  $con .= '</a>';
			if($m['link']) $con .= $this->rendering($m['link']);
			$con .=	'</li>';
		}

		$con .= '</ul>';

		return $con;
	}

}
