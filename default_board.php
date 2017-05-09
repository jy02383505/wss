<html>
<?php require_once('config/tank_config.php'); ?>
<?php require_once('session_unset.php'); ?>
<?php require_once('session.php'); ?>
<?php 
$url_this = $_SERVER["QUERY_STRING"] ;

$current_url = current(explode("&sort",$url_this));

$colyear_Recordset1 = date("m");

$where="";
$_SESSION['ser_year'] = $colyear_Recordset1;

if (isset($_GET['select_year'])) {
  $colyear_Recordset1 = $_GET['select_year'];
  $_SESSION['ser_year'] = $colyear_Recordset1;
}
if ($colyear_Recordset1 == date("Y")){
    $where =" and a.csa_plan_st like '%".date("Y")."%'";
} else if ($colyear_Recordset1 == date("m")){
    $where =" and a.csa_plan_st like '%".date("Y")."-".date("m")."%'";
} else if($colyear_Recordset1 == $_GET['select_year'] and $colyear_Recordset1 != '--'){
    $where =" and a.csa_plan_st >= '".date("Y-").$colyear_Recordset1."-01' and a.csa_plan_st < '".date('Y-').(intval($colyear_Recordset1)+1)."-01'";
}else{
    $where="";
}

$Recordset1="%";
$_SESSION['ser_createuser'] = $Recordset1;
if (isset($_GET['create_by'])) {
  $Recordset1 = $_GET['create_by'];
  $_SESSION['ser_createuser'] = $Recordset1;
  if($Recordset1!='%'){
    $where.="and a.csa_to_user='".$Recordset1."'";
  }
}

$currentPage = $_SERVER["PHP_SELF"];
mysql_select_db($database_tankdb, $tankdb);
$query_Recordset2 = "SELECT * FROM tk_user WHERE pid!=1 ORDER BY tk_display_name ASC";
$Recordset2 = mysql_query($query_Recordset2, $tankdb) or die(mysql_error());
$row_Recordset2 = mysql_fetch_assoc($Recordset2);
?>
<?php require('head.php'); ?>
<style type="text/css">
ul li{margin: 0;padding: 0; }
.all{width: 100%;margin:1% 0px 0px 0px;}
.plan{width: 24%;height:2em;float: left;margin: 0px 0px 0px 2%;border: 2px solid;border-bottom:1px solid;border-right: 0px;text-align:center;font-family:'STKaiti';font-weight:bold;font-size:25px;line-height:2em;}
.process{width: 24%;height:2em;float: left;border: 1px solid;border-top:2px solid;border-right: 0px;text-align:center;font-family:'STKaiti';font-weight:bold;font-size:25px;line-height:2em;}
.complete{width: 24%;height:2em;float: left;border: 1px solid;border-top:2px solid;border-right: 0px;text-align:center;font-family:'STKaiti';font-weight:bold;font-size:25px;line-height:2em;}
.check{width: 24%;height:2em;float: left;border: 1px solid;border-top:2px solid;border-right:2px solid;text-align:center;font-family:'STKaiti';font-weight:bold;font-size:25px;line-height:2em;}
.postpone{width: 96%;height:5em;border: 2px solid;margin: 0px 0px 0px 2%;line-height:5em;font-family:'STKaiti';font-size:25px;}
.clear{ margin: 0;padding: 0;clear:both;}
.fir{width: 24%;height:73%;float: left;border-left: 2px solid;border-right: 1px solid;border-bottom: 2px solid;margin: 0px 0px 0px 2%;overflow-y: scroll;}
.tk{width: 24%;height:73%;float: left;border-right: 1px solid;border-bottom: 2px solid;overflow-y: scroll;}
.ri{width: 24%;height:73%;float: left;border-right: 2px solid;border-bottom: 2px solid;overflow-y: scroll;}
.content_plan{float:left;margin: 15px 0px 0px 20px;padding: 5px; border: 1px solid;-moz-border-radius: 15px;-webkit-border-radius: 15px;border-radius:15px;  }
.content_process{float:left;margin: 15px 0px 0px 20px;padding: 5px; border: 1px solid;-moz-border-radius: 15px;-webkit-border-radius: 15px;border-radius:15px;  }
.content_complete{float:left;margin: 15px 0px 0px 20px;padding: 5px; border: 1px solid;-moz-border-radius: 15px;-webkit-border-radius: 15px;border-radius:15px;  }
.content_check{float:left;margin: 15px 0px 0px 20px;padding: 5px; border: 1px solid;-moz-border-radius: 15px;-webkit-border-radius: 15px;border-radius:15px;  }
.tasksearch{text-align:center;}
</style>
<div class="all">
<span>
<form id="form1" name="myform" method="get" class="tasksearch">
<select name="select_year" id="select_year">
        <option value="--">不限时间</option>
        
        <option value="<?php echo date("Y");?>" <?php if (isset($_SESSION['ser_year'])) {	
		if (!(strcmp(date("Y"), "{$_SESSION['ser_year']}"))) {
			echo "selected=\"selected\"";
			}
		} ?>>本年</option>

        <!-- 当前月份的过滤项 -->
        <?php 
        for ($i=0; $i < 12; $i++) { 
            $v = ($i+1)>9 ? ($i+1) : '0'.($i+1);
            echo "<option value='".$v."' ".($v==$_SESSION["ser_year"]?"selected":"").">".$v."月</option>";
        }
         ?>
      </select> 
     <select  name="create_by" id="create_by">
      <option value="%">不限执行人</option>
      <?php
            do {  
        ?>
      <option value="<?php echo $row_Recordset2['uid']?>"
	  <?php 
		if (isset($_SESSION['ser_createuser'])) {	
		if (!(strcmp($row_Recordset2['uid'], "{$_SESSION['ser_createuser']}"))) {
			echo "selected=\"selected\"";
			}
		}
 ?>><?php echo $row_Recordset2['tk_display_name']?></option>
      <?php
} while ($row_Recordset2 = mysql_fetch_assoc($Recordset2));
  $rows = mysql_num_rows($Recordset2);
  if($rows > 0) {
      mysql_data_seek($Recordset2, 0);
	  $row_Recordset2 = mysql_fetch_assoc($Recordset2);
  }
?>
    </select>
	</form></span>

    <div class="plan">
        计划中任务
                
    </div>
    <div class="process">
        进行中任务
    </div>
    <div class="complete">
        已完成或已验收任务
    </div>
    <div class="check">
        推迟或中断的任务
    </div>
    <div class="clear"></div>
    <div class="fir"> 
                <?php 
                    $plansql="SELECT * FROM tk_task as a,tk_status as b,tk_user as c,tk_project as d where a.csa_remark2=b.id and a.csa_to_user=c.uid and
                    a.csa_project=d.id and (a.csa_remark2=2 or a.csa_remark2=4) $where order by a.TID desc";
                    $planres = mysql_query($plansql,$tankdb);
                    while($res = mysql_fetch_assoc($planres)){ 
                  ?>      
        <div class="content_plan">
            <div>任务名称：<?php if(strpos($res['project_member'],",".trim($_SESSION['MM_uid']).",")||$_SESSION['MM_rank']==5){
                echo "<a href='default_task_edit.php?editID=". $res['TID']."'  target='_parent'>".$res['csa_text']."</a>";
                }else{
                    echo $res['csa_text'];
                }?></div>
            <div>任务执行人：<?php echo $res['tk_display_name']."&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";echo $res['csa_plan_st']." 至 ";echo $res['csa_plan_et'];?></div>
            <?php echo $res['task_status_display'];?>
        </div>
            <?php   }?>
    </div>
    <div class="tk">
            <?php 
                    $processsql="SELECT * FROM tk_task as a,tk_status as b,tk_user as c,tk_project as d where a.csa_remark2=b.id and a.csa_to_user=c.uid and
                    a.csa_project=d.id and (a.csa_remark2=5 or a.csa_remark2=6 or a.csa_remark2=7 or a.csa_remark2=8 or a.csa_remark2=9) $where order by a.TID desc";
                    $processres = mysql_query($processsql,$tankdb);
                    while($res = mysql_fetch_assoc($processres)){ 
                  ?>  
        <div class="content_process">
            <div>任务名称：<?php if(strpos($res['project_member'],",".trim($_SESSION['MM_uid']).",")||$_SESSION['MM_rank']==5){
                echo "<a href='default_task_edit.php?editID=". $res['TID']."'  target='_parent'>".$res['csa_text']."</a>";
                }else{
                    echo $res['csa_text'];
                }?></div>
            <div>任务执行人：<?php echo $res['tk_display_name']."&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";echo $res['csa_plan_st']." 至 ";echo $res['csa_plan_et'];?></div>
            <?php echo $res['task_status_display'];?>
        </div>
             <?php }?>
    </div>
    <div class="tk">
            <?php 
                    $completesql="SELECT * FROM tk_task as a,tk_status as b,tk_user as c,tk_project as d where a.csa_remark2=b.id and a.csa_to_user=c.uid and
                    a.csa_project=d.id and (a.csa_remark2=14 or a.csa_remark2=25) $where order by a.TID desc";
                    //echo $plansql;
                    $completeres = mysql_query($completesql,$tankdb);
                    while($res = mysql_fetch_assoc($completeres)){ 
                  ?>  
        <div class="content_complete">
            <div>任务名称：<?php if(strpos($res['project_member'],",".trim($_SESSION['MM_uid']).",")||$_SESSION['MM_rank']==5){
                echo "<a href='default_task_edit.php?editID=". $res['TID']."'  target='_parent'>".$res['csa_text']."</a>";
                }else{
                    echo $res['csa_text'];
                }?></div>
           <div>任务执行人：<?php echo $res['tk_display_name']."&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";echo $res['csa_plan_st']." 至 ";echo $res['csa_plan_et'];?></div>
            <?php echo $res['task_status_display'];?>
        </div>
            <?php }?>
    </div>
    <div class="ri">
            <?php 
                    $checksql="SELECT * FROM tk_task as a,tk_status as b,tk_user as c,tk_project as d where a.csa_remark2=b.id and a.csa_to_user=c.uid 
                    and a.csa_project=d.id and (a.csa_remark2=22 or a.csa_remark2=23) $where order by a.TID desc";
                    //echo $checksql;
                    $checkres = mysql_query($checksql,$tankdb);
                    while($res = mysql_fetch_assoc($checkres)){ 
                  ?>  
        <div class="content_check">
            <div>任务名称：<?php if(strpos($res['project_member'],",".trim($_SESSION['MM_uid']).",")||$_SESSION['MM_rank']==5){
                echo "<a href='default_task_edit.php?editID=". $res['TID']."'  target='_parent'>".$res['csa_text']."</a>";
                }else{
                    echo $res['csa_text'];
                }?></div>
            <div>任务执行人：<?php echo $res['tk_display_name']."&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";echo $res['csa_plan_st']." 至 ";echo $res['csa_plan_et'];?></div>
            <?php echo $res['task_status_display'];?>
        </div>
            <?php }?>
    </div>
    <div class="clear"></div>
</div>
<script>
$(function(){
    $('select').change(function(){
        $('#form1').submit();
    })
})
</script>
</body>
</html>