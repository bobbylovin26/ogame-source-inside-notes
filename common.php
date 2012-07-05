<?php

/**
 * common.php
 *
 * @version 1.0
 * @copyright 2008 by ppdream
 */

// 屏蔽所有的错误
error_reporting(0);

// 禁用全局数据库转义
set_magic_quotes_runtime(0);
$phpEx = "php";

$game_config   = array();
$user          = array();
$lang          = array();
$link          = "";
$IsUserChecked = false;

define('DEFAULT_SKINPATH' , 'skins/xnova/');
define('TEMPLATE_DIR'     , 'templates/');
define('TEMPLATE_NAME'    , 'OpenGame');
define('DEFAULT_LANG'     , 'cn');

$HTTP_ACCEPT_LANGUAGE = DEFAULT_LANG;

include($xnova_root_path . 'includes/debug.class.'.$phpEx);
$debug = new debug();

// 辅助函数，比如发送邮件和模版解析什么的
include($xnova_root_path . 'includes/functions.'.$phpEx);

// 计算星球距离，任务时间，速度因子等
include($xnova_root_path . 'includes/unlocalised.'.$phpEx);

// 业务层函数
include($xnova_root_path . 'includes/todofleetcontrol.'.$phpEx);

// 语言文件
include($xnova_root_path . 'language/'. DEFAULT_LANG .'/lang_info.cfg');

if (INSTALL != true) {
    // 各种数值以及名称和数值之间的映射
    include($xnova_root_path . 'includes/vars.'.$phpEx);

    // 包含具体的数据库处理函数
    include($xnova_root_path . 'includes/db.'.$phpEx);

    // 很简单的字符串处理函数
    include($xnova_root_path . 'includes/strings.'.$phpEx);

    // 查询游戏配置
    $query = doquery("SELECT * FROM {{table}}",'config');
    while ( $row = mysql_fetch_assoc($query) ) {
        // 直接只返回关联数组提高效率，丢弃数字索引数组
	    $game_config[$row['config_name']] = $row['config_value'];
    }

    // 设置常量，值大多来自于$game_config数组
	include($xnova_root_path . 'includes/constants.'.$phpEx);

    // 设置时区
	// date_default_timezone_set('Etc/GMT+2');
	putenv(TIMEZONE);
	
	if ($InLogin != true) {
        // 从cookies里面恢复用户，包括检查用户是不是被ban掉
        // 如果$IsUserChecked是false，内涵数会写cookies
		$Result        = CheckTheUser ( $IsUserChecked );
		$IsUserChecked = $Result['state'];

        // 当前用户的数据
		$user          = $Result['record'];
	} elseif ($InLogin == false) {
        // 系统被关闭状态
		if( $game_config['game_disable']) {
			if ($user['authlevel'] < 1) {
                // 给普通的玩家显示关闭的原因
				message ( stripslashes ( $game_config['close_reason'] ), $game_config['game_name'] );
			}
		}
	}

	includeLang ("system");
	includeLang ('tech');

	if ( isset ($user) ) {
        // 这里的代码怪怪的，不知道为什么
        // FlyingFleetHandler是处理航线事件的重要函数

        // 选出开始时间小于当前时间的函数
		$_fleets = doquery("SELECT * FROM {{table}} WHERE `fleet_start_time` <= '".time()."';", 'fleets'); //  OR fleet_end_time <= ".time()
		while ($row = mysql_fetch_array($_fleets)) {
			$array                = array();
			$array['galaxy']      = $row['fleet_start_galaxy'];
			$array['system']      = $row['fleet_start_system'];
			$array['planet']      = $row['fleet_start_planet'];
			$array['planet_type'] = $row['fleet_start_type'];

			$temp = FlyingFleetHandler ($array);
		}

        // 选出结束时间小于当前时间的函数
		$_fleets = doquery("SELECT * FROM {{table}} WHERE `fleet_end_time` <= '".time()."';", 'fleets'); //  OR fleet_end_time <= ".time()
		while ($row = mysql_fetch_array($_fleets)) {
			$array                = array();
			$array['galaxy']      = $row['fleet_end_galaxy'];
			$array['system']      = $row['fleet_end_system'];
			$array['planet']      = $row['fleet_end_planet'];
			$array['planet_type'] = $row['fleet_end_type'];

			$temp = FlyingFleetHandler ($array);
		}

        // 及时释放掉这个比较重的对象
		unset($_fleets);

        // 处理排名
		include($xnova_root_path . 'rak.'.$phpEx);

		if ( defined('IN_ADMIN') ) {
			$UserSkin  = $user['dpath'];
			$local     = stristr ( $UserSkin, "http:");

			if ($local === false) {
				if (!$user['dpath']) {
					$dpath     = "../". DEFAULT_SKINPATH  ;
				} else {
					$dpath     = "../". $user["dpath"];
				}
			} else {
				$dpath     = $UserSkin;
			}
		} else {
			$dpath     = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
		}

        // 根据GET参数设置当前星球
		SetSelectedPlanet ( $user );

        // 当前星球数据
		$planetrow = doquery("SELECT * FROM {{table}} WHERE `id` = '".$user['current_planet']."';", 'planets', true);
        // 坐标数据
		$galaxyrow = doquery("SELECT * FROM {{table}} WHERE `id_planet` = '".$planetrow['id']."';", 'galaxy', true);

        // 检查方圆数值是否需要更新
		CheckPlanetUsedFields($planetrow);
	} else {
        //当前用户无效
	}
} else {
	$dpath     = "../" . DEFAULT_SKINPATH;
}

?>
