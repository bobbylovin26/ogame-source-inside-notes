<?php

/**
 * buildings.php
 *
 * @version 1.3
 * @copyright 2008 by Chlorel for XNova
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

includeLang('buildings');

// $planetrow变量来自common.php文件，表示当前星球的数据，也有可能是
// 玩家上次退出的时候停留的星球，通过SetSelectedPlanet函数处理的
// UpdatePlanetBatimentQueueList会返回一个bool值
// 处理建筑队列
UpdatePlanetBatimentQueueList ( $planetrow, $user );

// 检查用户的科技在哪个星球，也有可能还没有
// 如果有科技研究完成也要处理
$IsWorking = HandleTechnologieBuild ( $planetrow, $user );

switch ($_GET['mode']) {
    case 'fleet':
        // --------------------------------------------------------------------------------------------------
        // 航线页面
        FleetBuildingPage ( $planetrow, $user );
        break;

    case 'research':
        // --------------------------------------------------------------------------------------------------
        ResearchBuildingPage ( $planetrow, $user, $IsWorking['OnWork'], $IsWorking['WorkOn'] );
        // 科技研究页面
        break;

    case 'defense':
        // --------------------------------------------------------------------------------------------------
        // 防御建筑
        DefensesBuildingPage ( $planetrow, $user );
        break;

    default:
        // --------------------------------------------------------------------------------------------------
        // 默认根据cmd参数处理升级、从队列中移除、新增、降级、取消，新增和降级是同一个函数处理的
        // 几个操作队列的函数
        // CancelBuildingFromQueue
        // RemoveBuildingFromQueue
        // AddBuildingToQueue(..., true/false)
        BatimentBuildingPage ( $planetrow, $user );
        break;
}

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Nettoyage modularisation
// 1.1 - Mise au point, mise en fonction pour lin�arisation du fonctionnement
// 1.2 - Liste de construction batiments
?>
