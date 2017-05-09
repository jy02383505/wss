<?php require_once('config/tank_config.php'); ?>
<?php require_once('session_unset.php'); ?>
<?php require_once('session.php'); ?>
<?php 
$pagetabs = "allfile";
if (isset($_GET['pagetab'])) {
  $pagetabs = $_GET['pagetab'];
}

$currentPage = $_SERVER["PHP_SELF"];
?>
<?php require('head.php'); ?>
<link href="skin/themes/base/custom.css" rel="stylesheet" type="text/css" />
<link rel="StyleSheet" href="dtree/dtree.css" type="text/css" />
<script type="text/javascript" src="srcipt/jquery.js"></script>
<script type="text/javascript" src="srcipt/js.js"></script>
<script type="text/javascript" src="srcipt/jqueryd.js"></script>
<script type="text/javascript" src="dtree/dtree.js"></script>

<style>
    .tree{
        float: left;
        width: 15%;
        overflow: auto;
        background-color: rgb(243, 243, 243);
    }
</style>

<div class="subnav">
    <ul class="nav nav-tabs">
        <li class="
        <?php if($pagetabs == "allfile") {
        echo "active";} ?>
        " ><a href="<?php echo $pagename; ?>?pagetab=allfile"><?php echo $multilingual_project_file_allfile;?></a></li>

        <li class="
        <?php if($pagetabs == "mcfile") {
        echo "active";} ?>
        "><a href="<?php echo $pagename; ?>?pagetab=mcfile"><?php echo $multilingual_project_file_myfile;?> </a></li>

        <li class="
        <?php if($pagetabs == "mefile") {
        echo "active";} ?>
        "><a href="<?php echo $pagename; ?>?pagetab=mefile"><?php echo $multilingual_project_file_myeditfile;?> </a></li>
    </ul>
    <div class="clearboth"></div>
</div>

<?php 
    if($pagetabs == 'allfile'){
        if($_SESSION[MM_uid] == '1'){
            $sql_project = "select id, project_name from tk_project";
        }else{
            $sql_project = "select id, project_name from tk_project where project_member like '%$_SESSION[MM_uid]%'";
        }
        $projects = Q($sql_project);
        $maxid_project = "select max(id) as maxid from tk_project";
        $maxid_project = Q($maxid_project);
        $maxid_project = $maxid_project[0][maxid];
        $sql_doc = "select * from tk_document";
        $documents = Q($sql_doc);

        $node_root[id] = 0;
        $node_root[pid] = -1;
        $node_root[name] = '所有文档';
        $node_root[url] = 'file.php';
        $node_root[title] = '所有文档';

        $node_project[id] = 1;
        $node_project[pid] = 0;
        $node_project[name] = '项目文档';
        $node_project[url] = 'file.php?projectpage=1&pagetab=allfile';
        $node_project[title] = '项目文档';

        $tree[0] = $node_root;
        $tree[1] = $node_project;

        foreach($projects as $project){
            $node[id] = $project[id] * 1 + 1;
            $node[pid] = 1;
            $node[name] = mb_strlen($project[project_name], 'utf-8') < 17 ? $project[project_name] : mb_substr($project[project_name], 0, 15, 'utf-8') . '...';
            $node[url] = "file.php?projectID=$project[id]&folder=1&pfile=1&$pagetabs";
            $node[title] = $project[project_name];
            $node[target] = '';
            $node[icon] = 'img/folder.gif';
            $tree[] = $node;
        }

        foreach($documents as $document){
            $node_doc[id] = $document[docid] * 1 + $maxid_project * 1 + 1;
            if($document[tk_doc_class1] == '-1' && $document[tk_doc_class2] == '-1'){
                // 根下的自建文档
                $node_doc[pid] = 0;
            }else if ($document[tk_doc_class1] != '-1' && $document[tk_doc_class2] == '0'){
                // 跟在某个项目下的文档(后自建)
                $node_doc[pid] = $document[tk_doc_class1] * 1 + 1;
            }else if ($document[tk_doc_class2] != '0'){
                $node_doc[pid] = $document[tk_doc_class2] * 1 + $maxid_project * 1 + 1;
            }else{
                $node_doc[pid] = 0;
            }
            $node_doc[name] = mb_strlen($document[tk_doc_title], 'utf-8') < 17 ? $document[tk_doc_title] : mb_substr($document[tk_doc_title], 0, 15, 'utf-8') . '...';
            if($document[tk_doc_backup1] == '1'){
                $node_doc[url] = "file.php?recordID=$document[docid]&folder=1&projectID=-1&pagetab=allfile";
                $node_doc[target] = '';
                $node_doc[icon] = 'img/folder.gif';
            }else{
                $node_doc[url] = "file_view.php?recordID=$document[docid]&projectID=-1&pagetab=allfile";
                $node_doc[target] = '_blank';
                $node_doc[icon] = 'img/page.gif';
            }
            $node_doc[title] = $document[tk_doc_title];
            $tree[] = $node_doc;
        }
    }else if($pagetabs == 'mcfile'){
        // 我创建的文档
        $sql_doc = "select * from tk_document where tk_doc_create = $_SESSION[MM_uid] and tk_doc_backup1=0 order by docid desc";
        $documents = Q($sql_doc);

        $node_root[id] = 0;
        $node_root[pid] = -1;
        $node_root[name] = '我创建的文档';
        $node_root[url] = 'file.php?pagetab=mcfile';
        $node_root[title] = '我创建的文档';

        $tree[0] = $node_root;

        foreach($documents as $document){
            $node_doc[id] = $document[docid];
            $node_doc[pid] = 0;
            $node_doc[name] = mb_strlen($document[tk_doc_title], 'utf-8') < 17 ? $document[tk_doc_title] : mb_substr($document[tk_doc_title], 0, 15, 'utf-8') . '...';
            $node_doc[url] = "file_view.php?recordID=$document[docid]&projectID=-1&pagetab=mcfile";
            $node_doc[target] = '_blank';
            $node_doc[icon] = 'img/page.gif';
            $node_doc[title] = $document[tk_doc_title];
            $tree[] = $node_doc;
        }
    }else{
        // 我编辑的文档
        $sql_doc = "select * from tk_document where tk_doc_edit = $_SESSION[MM_uid] and tk_doc_backup1=0 order by docid desc";
        $documents = Q($sql_doc);

        $node_root[id] = 0;
        $node_root[pid] = -1;
        $node_root[name] = '我编辑的文档';
        $node_root[url] = 'file.php?pagetab=mefile';
        $node_root[title] = '我编辑的文档';

        $tree[0] = $node_root;

        foreach($documents as $document){
            $node_doc[id] = $document[docid];
            $node_doc[pid] = 0;
            $node_doc[name] = mb_strlen($document[tk_doc_title], 'utf-8') < 17 ? $document[tk_doc_title] : mb_substr($document[tk_doc_title], 0, 15, 'utf-8') . '...';
            $node_doc[url] = "file_view.php?recordID=$document[docid]&projectID=-1&pagetab=mefile";
            $node_doc[target] = '_blank';
            $node_doc[icon] = 'img/page.gif';
            $node_doc[title] = $document[tk_doc_title];
            $tree[] = $node_doc;
        }
    }

    $treeJson = json_encode($tree);
 ?>
<div class="tree">
    <script>
        $(function(){
            // style for 1366
            var windowHeight = $(window).height();
            $('.tree').css('height', (windowHeight - 40 - parseInt($('.subnav').css('height')) - 40) + 'px');
            var windowWidth = $(window).width();
            if(windowWidth < 1366){
                $('.pagemargin').css('width', (windowWidth * 0.77) + 'px');
                $('.tree').css({
                    'width': (windowWidth * 0.2) + 'px',
                });
            }
        })
        // generate dtree nodes
        var treeData = <?php echo $treeJson; ?>;
        // d.add(id, pid, name, url, title, target, icon, iconOpen, open);
        d = new dTree('d');
        $.each(treeData, function(i, o){
            d.add(o.id, o.pid, o.name, o.url, o.title, o.target, o.icon);
        })
        document.write(d);
    </script>
</div>

<div class="pagemargin">
<?php require('control_file.php'); ?>
</div>

<?php require('foot.php'); ?>

</body>
</html>