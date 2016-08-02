<?php

/**
 * Author:   yutanghua
 * Desc:     获取分页的html代码,根据模板显示,如果CSS-js修改 则需要相应的修改
 * $p：      显示第几页
 * $count：  共显示的数据记录条数
 * $url：    分页的网址如： ’/news/index.html?p=1‘ 使用传递：‘/news/index.html’
 * $size：   每页显示条数
 * time：2015.10.09
 */
function getPageHtml($p,$count,$url,$size = 20,$keywords='')
{
	//计算总的页数
	if($count % $size == 0)
		$pages = (int)($count / $size);
		else
			$pages = (int)($count / $size) + 1;
			$html = "<span class='pagecount'>每页显示".$size."条记录 (共".$count."条/".$pages."页记录)</span>";
			//如果只有一页  则不需要分页信息
			if($pages > 1){
				$html .= "<div class='pages'>";
				if($p == 1){
					//没有上一页 则连接为空
					$html .= "<a href='#' class='first'>首页</a>";
					$html .= "<a href='#' class='previous'>上一页</a>";
				}else{
					$html .= "<a href='".$url."?p=1&keywords=$keywords' class='first' >首页</a>";
					$html .= "<a href='".$url."?p=".($p-1)."&keywords=$keywords' class='previous'>上一页</a>";
				}
				$html .= "<span>";
				//计算当前页的所在的第一页（11.12.13.14.15  第一页为11  ）
				$firstPage = 1;
				for($i = 0;$i < $pages/5 ;$i++){
					if(($i+1)*5 > $p - 1){
						$firstPage = $i*5+1; //取到了就可以跳出
						break;
					}
				}
				//如果是最后一页   则判断下与最大页数比较 取小
				$total = ($firstPage+5) < ($pages+1)?($firstPage+5):($pages+1);
				//循环输出当前页  即输出（11.12.13.14.15）
				for($i = $firstPage;$i < $total ;$i++){
					if($i == $p)
						$html .= "<a href='#' class='current'>".$i."</a>";
						else
							$html .= "<a href='$url?p=$i&keywords=$keywords'>$i</a>";
				}
				//
				$html .= "</span>";
				if($p == $pages){
					//没有下一页 则连接为空
					$html .= "<a href='#' class='next'>下一页</a>";
					$html .= "<a href='#' class='last'>尾页</a>";
				}else{
					$html .= "<a href='".$url."?p=".($p+1)."&keywords=$keywords' class='next'>下一页</a>";
					$html .= "<a href='".$url."?p=".$pages."&keywords=$keywords' class='last'>尾页</a>";
				}
				$html .= "</div>";
			}
			return $html;
}

/**
 * Author:   yutanghua
 * @param [type]  $node [要处理的节点数组]
 * @param integer  $pid [父级id]
 * time：2015.10.11
 */
function node_merge($node,$access=null,$pid = 0){
	$arr = array();
	$num = 0;
	foreach($node as $v){
		if(is_array($access)){
			$v['access'] = in_array($v['id'], $access)?1:0;
		}
		if($v['pid'] == $pid){
			$v['child'] = node_merge($node,$access,$v['id']);
			//如果权限，则排序加1
			$v['num'] = $num++;
			$arr[] = $v;
		}
	}
	return $arr;
}

/*
 * 根据id 排序数组  多级目录取前2级目录
 * time：2015.09.16
 * by：yutanghua
 */
function g_mergeArrayTop2Level($data,$idname,$html=' - ',$pid=0,$level=0){
	$arr = array();
	foreach ($data as $v){
		if($v['parentid'] == $pid){
			$v['level'] = 1;
			$v['html'] = '';
			$arr[] = $v;
			foreach ($data as $v2){
				if($v2['parentid'] == $v[$idname]){
					$v2['html'] = $html;
					$v2['level'] = 2;
					$arr[] = $v2;
				}
			}//foreach
		}//if
	}//foreach
	return $arr;
}

/*
 * 根据id 排序数组  无限极排序
 * time：2015.09.16
 * by：yutanghua
 */
function g_mergeArray($data,$idname,$html=' _ ',$pid=0,$level=0){
	$arr = array();
	foreach ($data as $v){
		if($v['parentid'] == $pid){
			$v['html'] = str_repeat($html, $level);
			$v['level'] = $level + 1;
			$arr[] = $v;
			$child = g_mergeArray($data,$idname,$html,$v[$idname],$v['level']);
			$arr = array_merge($arr, $child);
		}
	}
	return $arr;
}

/**
 * Author:   yutanghua
 * Desc:     获取分页的html代码,根据模板显示,如果CSS-js修改 则需要相应的修改
 * $p：      显示第几页
 * $count：  共显示的数据记录条数
 * $url：    分页的网址如： ’/news/index.html?p=1‘ 使用传递：‘/news/index.html’
 * $size：   每页显示条数
 * time：2015.10.09    2016.1.3 异步分页
 */
function getPageHtmlPOST($p,$count,$size = 20)
{
	//计算总的页数
	if($count % $size == 0)
		$pages = (int)($count / $size);
		else
			$pages = (int)($count / $size) + 1;
			$html = "<span class='pagecount'>每页显示".$size."条记录 (共".$count."条/".$pages."页记录)</span>";
			//如果只有一页  则不需要分页信息
			if($pages > 1){
				$html .= "<div class='pages'>";
				if($p == 1){
					//没有上一页 则连接为空
					$html .= "<a href='javascript:;' class='first'>首页</a>";
					$html .= "<a href='javascript:;' class='previous'>上一页</a>";
				}else{
					$html .= "<a href='javascript:setpage(".$p.");' class='first' >首页</a>";
					$html .= "<a href='javascript:setpage(".$p.");' class='previous'>上一页</a>";
				}
				$html .= "<span>";
				//计算当前页的所在的第一页（11.12.13.14.15  第一页为11  ）
				$firstPage = 1;
				for($i = 0;$i < $pages/5 ;$i++){
					if(($i+1)*5 > $p - 1){
						$firstPage = $i*5+1; //取到了就可以跳出
						break;
					}
				}
				//如果是最后一页   则判断下与最大页数比较 取小
				$total = ($firstPage+5) < ($pages+1)?($firstPage+5):($pages+1);
				//循环输出当前页  即输出（11.12.13.14.15）
				for($i = $firstPage;$i < $total ;$i++){
					if($i == $p)
						$html .= "<a href='javascript:;' class='current'>".$i."</a>";
						else
							$html .= "<a href='javascript:setpage(".$i.");'>$i</a>";
				}
				//
				$html .= "</span>";
				if($p == $pages){
					//没有下一页 则连接为空
					$html .= "<a href='javascript:;' class='next'>下一页</a>";
					$html .= "<a href='javascript:;' class='last'>尾页</a>";
				}else{
					$html .= "<a href='javascript:setpage(".($p+1).");' class='next'>下一页</a>";
					$html .= "<a href='javascript:setpage(".($pages).");' class='last'>尾页</a>";
				}
				$html .= "</div>";
			}
			return $html;
}