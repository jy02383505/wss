<?php require_once('config/tank_config.php'); ?>
<?php require_once('session_unset.php'); ?>
<?php require_once('session.php'); ?>
<?php 
// 将时间戳转换为毫秒级
function st_format($str_st){
    return '/Date('.(strtotime($str_st) * 1000).')/';
}

// 将时间戳转换为毫秒级，同时给出结束时间点默认值为开始后的30天。
function et_format($str_st, $str_et){
    return '/Date('.(strtotime($str_et) ? strtotime($str_et) * 1000 : $value['project_start']+3600*24*30*1000).')/';
}

 ?>
<?php 
$project_id = $_GET['project_id']?:56;
// $pdo = new PDO("mysql:host=$hostname_tankdb;dbname=$database_tankdb", $username_tankdb, $password_tankdb) or die("MySQL Connection error...");
$sql = "select a.id, a.project_name, a.project_start, a.project_end, a.project_code, (select tk_display_name from tk_user where uid=a.project_to_user) pInCharge, a.project_member, b.task_status from tk_project a left join tk_status_project b on a.project_status=b.psid having a.id=$project_id order by a.project_start desc";
// $smt = $pdo->query($sql);
$rows = Q($sql);
// $rows = $smt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $key => &$value){
    // 处理项目成员
    $msql = "select tk_display_name from tk_user where uid in (".trim($value['project_member'], ',').")";
    $members = Q($msql);
    // $members = $pdo->query($msql)->fetchAll(PDO::FETCH_ASSOC);
    foreach($members as $v){
        $value['members'][] = $v['tk_display_name'];
    }
    $value['members'] = join(',', $value['members']);

    // 转时间戳，要求精确到毫秒级
    $value['project_start'] = st_format($value['project_start']);
    $value['project_end'] = et_format($value['project_start'], $value['project_end']);

    //处理该项目下所有的任务
    $tsql = "select tid task_id, csa_text task_name, (select task_status_display from tk_status where id=csa_remark2) status_display, csa_remark4, (select tk_display_name from tk_user where tk_user.uid=tk_task.csa_to_user) uname, csa_plan_st, csa_plan_et from tk_task where csa_project=".$value['id'];
    $tasks = Q($tsql);
    foreach($tasks as $tk => $tv){
        $value['tasks'][$tk]['task_id'] = $tv['task_id'];
        $value['tasks'][$tk]['task_name'] = $tv['task_name'];
        $value['tasks'][$tk]['task_status_display'] = $tv['status_display'];
        $value['tasks'][$tk]['task_pid'] = $tv['csa_remark4'];
        $value['tasks'][$tk]['inCharge'] = $tv['uname'];
        $value['tasks'][$tk]['st'] = st_format($tv['csa_plan_st']);
        $value['tasks'][$tk]['et'] = et_format($tv['csa_plan_st'], $tv['csa_plan_et']);
    }

    // 将任务父子关系呈现出来
    foreach($value['tasks'] as &$taskv0){
        foreach($value['tasks'] as $taskv1){
            if($taskv1['task_pid'] == $taskv0['task_id']){
                $taskv0['children'][] = $taskv1;
            }
        }
    }

    // 将重复的子任务删除
    foreach($value['tasks'] as $taskk2 => &$taskv2){
        if($taskv2['task_pid'] != '-1'){
            unset($value['tasks'][$taskk2]);
        }
    }
}

function make_gantt($data=null){
    $result = array();
    $first['id'] = $data['id'];
    $first['name'] = "<span style='color:blue;'>项目:</span><a href='project_view.php?recordID=".$data['id']."&pagetab=$_GET[pagetab]'>".$data['project_name']."</a>";
    $first['values'][0]['from'] = $data['project_start'];
    $first['values'][0]['to'] = $data['project_end'];
    $first['values'][0]['label'] = $data['project_name'];
    $first['values'][0]['customClass'] = 'ganttRed';
    $first['values'][0]['desc'] = '<b style="color:#f00;">项目负责人：'.$data['pInCharge'].'</b> <b style="color:purple;">项目成员：</b>'.$data['members'].'';
    array_push($result, $first);

    foreach($data['tasks'] as $gantt_key => $gantt_value){
        if(isset($gantt_value['children'])){
            $temp['task_id'] = $gantt_value['task_id'];
            $temp['name'] = '<div style="float:left;" class="task_status">'.$gantt_value['task_status_display']."</div>&nbsp;<a href='default_task_edit.php?editID=".$gantt_value['task_id']."&pagetab=$_GET[pagetab]'>".$gantt_value['task_name']."</a>";
            $temp['values'][0]['from'] = $gantt_value['st'];
            $temp['values'][0]['to'] = $gantt_value['et'];
            $temp['values'][0]['label'] = $gantt_value['task_name'];
            $temp['values'][0]['customClass'] = 'ganttOrange';
            $temp['values'][0]['desc'] = '<b style="color:maroon;">'.$gantt_value['task_name'].'</b>(<b style="color:purple;">负责人：</b>'.$gantt_value['inCharge'].')';
            array_push($result, $temp);

            foreach($gantt_value['children'] as $gantt_c_value){
                $temp['task_id'] = $gantt_c_value['task_id'];
                $temp['name'] = '<div style="float:left;" class="task_status">'.$gantt_c_value['task_status_display']."</div>&nbsp;<a href='default_task_edit.php?editID=".$gantt_c_value['task_id']."&pagetab=$_GET[pagetab]'>".$gantt_c_value['task_name']."</a>";
                $temp['values'][0]['from'] = $gantt_c_value['st'];
                $temp['values'][0]['to'] = $gantt_c_value['et'];
                $temp['values'][0]['label'] = $gantt_c_value['task_name'];
                $temp['values'][0]['customClass'] = 'ganttGreen';
                $temp['values'][0]['desc'] = '<b style="color:maroon;">'.$gantt_c_value['task_name'].'</b>(<b style="color:purple;">负责人：</b>'.$gantt_c_value['inCharge'].')';
                array_push($result, $temp);
            }
        }else{
            $temp['task_id'] = $gantt_value['task_id'];
            $temp['name'] = '<div style="float:left;" class="task_status">'.$gantt_value['task_status_display']."</div>&nbsp;<a href='default_task_edit.php?editID=".$gantt_value['task_id']."&pagetab=$_GET[pagetab]'>".$gantt_value['task_name']."</a>";
            $temp['values'][0]['from'] = $gantt_value['st'];
            $temp['values'][0]['to'] = $gantt_value['et'];
            $temp['values'][0]['label'] = $gantt_value['task_name'];
            $temp['values'][0]['customClass'] = 'ganttBlue';
            $temp['values'][0]['desc'] = '<b style="color:maroon;">'.$gantt_value['task_name'].'</b>(<b style="color:purple;">负责人：</b>'.$gantt_value['inCharge'].')';
            array_push($result, $temp);
        }
    }
    return $result;
}
$r = json_encode(make_gantt($rows[0]));

 ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>久联任务项目管理系统</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="gantt-jquery/css/style.css">
</head>
<body>
    <?php require('head.php'); ?>
    <button class="btn btn-info goBack" style="margin: 10px 0 0 15px;border-radius:15px;outline:0px;"><i class="icon-white icon-arrow-left"></i></button>
    <div class="gantt"></div>
    <?php require('foot.php'); ?>

<script src="srcipt/jquery.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="gantt-jquery/js/jquery.fn.gantt.min.js"></script>
<script>
    $(function(){
        $('.gantt').gantt({
            source: <?php echo $r; ?>,
            maxScale: "months",
            months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
            dow: ["日", "一", "二", "三", "四", "五", "六"],
            navigate: "scroll",
            itemsPerPage: 20,
            waitText: "加载中，请微等...",
        })
    })
    $('.goBack').click(function(){
        history.go(-1);
    })
</script>
</body>
</html>