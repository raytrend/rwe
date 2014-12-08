<?php
namespace app\extensions\util;

class StringUtil {
	
	/**
	 * 字符串截取
	 * 
	 * @author Joseph
	 */
	public static function truncate($str, $sublen = 120, $ending = '...', $start = 0) {
		if (mb_strlen($str, 'UTF-8') > $sublen) {
			$str = mb_substr($str, $start, $sublen, 'utf-8');
			$str .= $ending;
		} 
	
		return $str;
	}
	
	/**
	 * HTML 样式清理, 将一段富文本中的所有类似 font, span 等的样式都去除掉,
	 * 只剩下包含数据和最基本的 br, p, strong 标签等.
	 * 
	 * @param content 未清理前的富文本内容
	 * @return 清理后的富文本内容
	 */
	public static function html_purify_for_mobile($content) {
		// 去掉多余的空行
		$content = preg_replace('/(<div>\s*<br\s*[\/]?>\s*<\/div>)+/i', '<div><br /></div>', $content);
		
		// 处理HTML标签
		$pattern = "/(<[^\/]\S*?)(\s[\s\S]*?)?(\/*?>)/i";
		$content = preg_replace_callback($pattern, function ($matches) {
			//echo $matches[2] . "<br/>";
			switch (strtolower($matches[1])) {
				case '<a'    :
					return $matches[1] . ' onclick="void(0)" ' . $matches[2] . $matches[3];
				// span 和 font 等样式全部去除
				case '<span' :
					return '';
				case '<font' :
					return $matches[1]. (preg_replace('/(size|face)\s*=\s*"[^"]*"/i', '', $matches[2])). $matches[3];
				// img 的 width 和 height 全部去除, 然后 css 设置成为最大宽度的 100%, img { max-width: 100%; }
				// TODO 后期应该加入自适应屏幕的功能, 如果 width 超过屏幕则进行裁剪
				case '<img' :
					preg_match_all('/([^ \f\n\r\t\v=]+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?/i', $matches[0], $result, PREG_PATTERN_ORDER);
					$attrs = array ();
					$tags = $result[1];
					$values = $result[2];
					for($i = 0; $i < count($tags); $i ++) {
						$attrs[$tags[$i]] = $values[$i];
					}
					$src = (empty($attrs['file'])) ? $attrs['src'] : $src = $attrs['file'];

					// 把图片的 width, height 等属性去除后只显示 src 属性, 并判断是否勾选了“原图显示”复选框
					// nothumb=true: 显示原图
					if (empty($src)) {
						return '';
					} else if (strpos($src, 'nothumb=') === false || strpos($src, 'nothumb=false') === false) {
						return '<img src="' . $src . '" />';
					} else {
						return '<img data-original="' . $src . '" class="imageview" />';
					}
				default :
					if (strpos($matches[2], 'aimg_tip')) {
						return $matches[0];
					}
					return $matches[1] . $matches[3];
			}
		}, $content);
		
		$pattern = "/(<\/[\s\S]*?>)/i";
		$content = preg_replace_callback($pattern, function ($matches) {
			switch (strtolower($matches[1])) {
				case '</span>' :
				//case '</font>' :
					return '';
				default :
					return $matches[0];
			}
		}, $content);
		
		return $content;
	}
	
	public static function br2nl($content) {
		return preg_replace('/<br\s+\/?' . '>/i', "\r\n", str_replace(array("\r", "\n"), "", $content));
	}
	
	/**
	 * HTML 样式清理, 除了 br 以外的所有标签都清理掉
	 *
	 * @author jianqin
	 * @param content 未清理前的富文本内容
	 * @return 清理后文本内容
	 */
	public static function html_clear($content) {
		// 去掉多余的空行
		$content = preg_replace('/(<div>\s*<br\s*[\/]?>\s*<\/div>)+/i', '<br />', $content);
		
		// <div>xxx</div> => xxx<br />
		$content = preg_replace('/<div[^>]*>([^<]*)<\/div>/i', '$1<br />', $content);
	
		// <a href="url">xxx</a> => xxx(url)
		$content = preg_replace('/<a[^>]+href\s*=\s*["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/i', '$2($1)', $content);
		
		// 除br外，其他html标签清理
		$content = strip_tags($content, '<br>');
		
		return $content;
	}
	
	public static function has_html($content) {
		return preg_match('/<[^>]*>/i', $content);
	}
	
	/**
	 * 15x7=105个qq表情.
	 * 
	 * @param string $content
	 * @return mixed|string
	 */
	public static function replace_qq_face($content) {
		$pattern = "{/::\\)|/::~|/::B|/::\\||/:8-\\)|/::<|/::$|/::X|/::Z|/::'\\(|/::-\\||/::@|/::P|/::D|/::O|/::\\(|/::\\+|/:--b|/::Q|/::T|/:,@P|/:,@-D|/::d|/:,@o|/::g|/:\\|-\\)|/::!|/::L|/::>|/::,@|/:,@f|/::-S|/:\\?|/:,@x|/:,@@|/::8|/:,@!|/:!!!|/:xx|/:bye|/:wipe|/:dig|/:handclap|/:&-\\(|/:B-\\)|/:<@|/:@>|/::-O|/:>-\\||/:P-\\(|/::'\\||/:X-\\)|/::\\*|/:@x|/:8\\*|/:pd|/:<W>|/:beer|/:basketb|/:oo|/:coffee|/:eat|/:pig|/:rose|/:fade|/:showlove|/:heart|/:break|/:cake|/:li|/:bome|/:kn|/:footb|/:ladybug|/:shit|/:moon|/:sun|/:gift|/:hug|/:strong|/:weak|/:share|/:v|/:@\\)|/:jj|/:@@|/:bad|/:lvu|/:no|/:ok|/:love|/:<L>|/:jump|/:shake|/:<O>|/:circle|/:kotow|/:turn|/:skip|/:oY|/:#-0|/:hiphot|/:kiss|/:<&|/:&>}";
		return preg_replace_callback($pattern, function ($matches) {
			switch ($matches[0]) {
				case '/::)':
					return '/微笑';
				case '/::~':
					return '/撇嘴';
				case '/::B':
					return '/色';
				case '/::|':
					return '/发呆';
				case '/:8-)':
					return '/得意';
				case '/::<':
					return '/流泪';
				case '/::$':
					return '/害羞';
				case '/::X':
					return '/闭嘴';
				case '/::Z':
					return '/睡';
				case "/::'(":
					return '/大哭';
				case '/::-|':
					return '/尴尬';
				case '/::@':
					return '/发怒';
				case '/::P':
					return '/调皮';
				case '/::D':
					return '/呲牙';
				case '/::O':
					return '/惊讶';
					
				case '/::(':
					return '/难过';
				case '/::+':
					return '/酷';
				case '/:--b':
					return '/冷汗';
				case '/::Q':
					return '/抓狂';
				case '/::T':
					return '/吐';
				case '/:,@P':
					return '/偷笑';
				case '/:,@-D':
					return '/可爱';
				case '/::d':
					return '/白眼';
				case '/:,@o':
					return '/傲慢';
				case '/::g':
					return '/饥饿';
				case '/:|-)':
					return '/困';
				case '/::!':
					return '/惊恐';
				case '/::L':
					return '/流汗';
				case '/::>':
					return '/憨笑';
				case '/::,@':
					return '/大兵';
					
				case "/:,@f":
					return '/努力';
				case "/::-S":
					return '/咒骂';
				case "/:?":
					return '/疑问';
				case "/:,@x":
					return '/嘘';
				case "/:,@@":
					return '/晕';
				case "/::8":
					return '/折磨';
				case "/:,@!":
					return '/衰';
				case "/:!!!":
					return '/骷髅';
				case "/:xx":
					return '/敲打';
				case "/:bye":
					return '/再见';
				case "/:wipe":
					return '/擦汗';
				case "/:dig":
					return '/抠鼻';
				case "/:handclap":
					return '/鼓掌';
				case "/:&-(":
					return '/溴大了';
				case "/:B-)":
					return '/坏笑';
					
				case "/:<@":
					return '/左哼哼';
				case "/:@>":
					return '/右哼哼';
				case "/::-O":
					return '/哈欠';
				case "/:>-|":
					return '/鄙视';
				case "/:P-(":
					return '/委屈';
				case "/::'|":
					return '/快哭了';
				case "/:X-)":
					return '/阴险';
				case "/::*":
					return '/亲亲';
				case "/:@x":
					return '/吓';
				case "/:8*":
					return '/可怜';
				case "/:pd":
					return '/菜刀';
				case "/:<W>":
					return '/西瓜';
				case "/:beer":
					return '/啤酒';
				case "/:basketb":
					return '/篮球';
				case "/:oo":
					return '/乒乓';
					
				case "/:coffee":
					return '/咖啡';
				case "/:eat":
					return '/饭';
				case "/:pig":
					return '/猪头';
				case "/:rose":
					return '/玫瑰';
				case "/:fade":
					return '/凋谢';
				case "/:showlove":
					return '/示爱';
				case "/:heart":
					return '/爱心';
				case "/:break":
					return '/心碎';
				case "/:cake":
					return '/蛋糕';
				case "/:li":
					return '/闪电';
				case "/:bome":
					return '/炸弹';
				case "/:kn":
					return '/刀';
				case "/:footb":
					return '/足球';
				case "/:ladybug":
					return '/瓢虫';
				case "/:shit":
					return '/便便';
					
				case "/:moon":
					return '/月亮';
				case "/:sun":
					return '/太阳';
				case "/:gift":
					return '/礼物';
				case "/:hug":
					return '/拥抱';
				case "/:strong":
					return '/强';
				case "/:weak":
					return '/弱';
				case "/:share":
					return '/握手';
				case "/:v":
					return '/胜利';
				case "/:@)":
					return '/抱拳';
				case "/:jj":
					return '/勾引';
				case "/:@@":
					return '/拳头';
				case "/:bad":
					return '/差劲';
				case "/:lvu":
					return '/爱你';
				case "/:no":
					return '/No';
				case "/:ok":
					return '/Ok';
					
				case "/:love":
					return '/爱情';
				case "/:<L>":
					return '/飞吻';
				case "/:jump":
					return '/跳舞';
				case "/:shake":
					return '/发抖';
				case "/:<O>":
					return '/怄火';
				case "/:circle":
					return '/转圈';
				case "/:kotow":
					return '/磕头';
				case "/:turn":
					return '/回头';
				case "/:skip":
					return '/跳绳';
				case "/:oY":
					return '/挥手';
				case "/:#-0":
					return '/激动';
				case "/:hiphot":
					return '/街舞';
				case "/:kiss":
					return '/献吻';
				case "/:<&":
					return '/左太极';
				case "/:&>":
					return '/右太极';
			}
		}, $content);
	}
}