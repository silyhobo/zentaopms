<?php
/**
 * The task group view file of project module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     project
 * @version     $Id: grouptask.html.php 4143 2013-01-18 07:01:06Z wyd621@gmail.com $
 * @link        http://www.zentao.net
 */
?>
<?php include '../../common/view/header.html.php';?>
<?php include './taskheader.html.php';?>
<?php if(isset($lang->project->groupFilter[$groupBy])):?>
<?php $currentFilter = empty($filter) ? key($lang->project->groupFilter[$groupBy]) : $filter;?>
<div class='sub-featurebar'>
  <ul class='nav nav-tabs'>
    <?php foreach($lang->project->groupFilter[$groupBy] as $filterKey => $name):?>
    <li <?php if($filterKey == $currentFilter) echo "class='active'"?>><?php echo html::a(inlink('grouptask', "projectID=$projectID&groupBy=$groupBy&filter=$filterKey"), $name)?></li>
    <?php endforeach;?>
  </ul>
</div>
<?php endif;?>
<table class='table active-disabled table-condensed table-fixed' id='groupTable'>
  <thead>
    <tr>
      <th class='w-200px text-left'>
        <?php echo html::a('###', "<i class='icon-caret-down'></i> " . $lang->task->$groupBy, '', "class='expandAll' data-action='expand'")?>
        <?php echo html::a('###', "<i class='icon-caret-right'></i> " . $lang->task->$groupBy, '', "class='collapseAll hidden' data-action='collapse'")?>
      </th>
      <th class='w-id'><?php echo $lang->task->id;?></th>
      <th class='w-pri'> <?php echo $lang->priAB;?></th>
      <th><?php echo $lang->task->name;?></th>
      <th class='w-80px'><?php echo $lang->task->status;?></th>
      <th class='w-80px'><?php echo $lang->task->deadlineAB;?></th>
      <th class='w-user'><?php echo $lang->task->assignedTo;?></th>
      <th class='w-user'><?php echo $lang->task->finishedBy;?></th>
      <th class='w-50px'><?php echo $lang->task->estimateAB;?></th>
      <th class='w-50px'><?php echo $lang->task->consumedAB;?></th>
      <th class='w-50px'><?php echo $lang->task->leftAB;?></th>
      <th class='w-50px'><?php echo $lang->task->progess;?></th>
      <th class='w-50px'><?php echo $lang->typeAB;?></th>
      <th class='w-60px'><?php echo $lang->actions;?></th>
    </tr>
  </thead>
  <?php  
    $taskSum       = 0;
    $statusWait    = 0;
    $statusDone    = 0;
    $statusDoing   = 0;
    $statusClosed  = 0;  
    $totalEstimate = 0.0;
    $totalConsumed = 0.0;
    $totalLeft     = 0.0;
  ?>
  <?php
  if($groupBy == 'finishedBy') unset($tasks['']);
  if($groupBy == 'closedBy') unset($tasks['']);
  ?>
  <?php foreach($tasks as $groupKey => $groupTasks):?>
  <?php $i = 0;?>
  <?php
    $groupWait     = 0;
    $groupDone     = 0;
    $groupDoing    = 0;
    $groupClosed   = 0;  
    $groupEstimate = 0.0;
    $groupConsumed = 0.0;
    $groupLeft     = 0.0;

    $groupName = $groupKey;
    if($groupBy == 'story') $groupName = empty($groupName) ? $this->lang->task->noStory : zget($groupByList, $groupKey);
    if($groupBy == 'assignedTo' and $groupName == '') $groupName = $this->lang->task->noAssigned;
  ?>
  <tbody>
  <?php foreach($groupTasks as $task):?>
  <?php
  if(isset($currentFilter) and $currentFilter != 'all')
  {
      if($groupBy == 'story'      and $currentFilter == 'linked' and empty($task->story)) continue;
      if($groupBy == 'pri'        and $currentFilter == 'noset'  and !empty($task->pri)) continue;
      if($groupBy == 'assignedTo' and $currentFilter == 'undone' and $task->status != 'wait' and $task->status != 'doing') continue;
  }
  ?>
  <?php $assignedToClass = $task->assignedTo == $app->user->account ? "style='color:red'" : '';?>
  <?php $taskLink        = $this->createLink('task','view',"taskID=$task->id"); ?>
  <?php  
    $totalEstimate  += $task->estimate;
    $totalConsumed  += $task->consumed;
    $totalLeft      += ($task->status == 'cancel' ? 0 : $task->left);

    $groupEstimate  += $task->estimate;
    $groupConsumed  += $task->consumed;
    $groupLeft      += ($task->status == 'cancel' ? 0 : $task->left);

    if($task->status == 'wait')
    {
        $statusWait++;
        $groupWait++;
    }
    elseif($task->status == 'doing')
    {
        $statusDoing++;
        $groupDoing++;
    }
    elseif($task->status == 'done')
    {
        $statusDone++;
        $groupDone++;
    }
    elseif($task->status == 'closed')
    {
        $statusClosed++;
        $groupClosed++;
    }
    $groupSum = count($groupTasks);
    $taskSum += count($tasks);
   ?>
    <tr class='text-center'>
      <?php if($i == 0):?>
      <td rowspan='<?php echo count($groupTasks) + 1?>' class='groupby text-left'>
        <?php echo html::a('###', "<i class='icon-caret-down'></i> " . $groupName, '', "class='expandGroup' data-action='expand' title='$groupName'");?>
        <div class='groupSummary text' style='white-space:normal'>
        <?php if($groupBy == 'assignedTo' and isset($members[$task->assignedTo])) printf($lang->project->memberHours, $users[$task->assignedTo], $members[$task->assignedTo]->totalHours);?>
        <?php printf($lang->project->groupSummary, $groupSum, $groupWait, $groupDoing, $groupEstimate, $groupConsumed, $groupLeft);?></div>
      </td>
      <?php endif;?>
      <td><?php echo $task->id;?></td>
      <td><span class='<?php echo 'pri' . zget($lang->task->priList, $task->pri, $task->pri)?>'><?php echo zget($lang->task->priList, $task->pri, $task->pri);?></span></td>
      <td class='text-left'><?php if(!common::printLink('task', 'view', "task=$task->id", $task->name)) echo $task->name;?></td>
      <td class=<?php echo $task->status;?> ><?php echo $lang->task->statusList[$task->status];?></td>
      <td class=<?php if(isset($task->delay)) echo 'delayed';?>><?php if(substr($task->deadline, 0, 4) > 0) echo $task->deadline;?></td>
      <td <?php echo $assignedToClass;?>><?php echo $task->assignedToRealName;?></td>
      <td><?php echo $users[$task->finishedBy];?></td>
      <td><?php echo $task->estimate;?></td>
      <td><?php echo $task->consumed;?></td>
      <td><?php echo $task->left;?></td>
      <td class='text-left'><?php echo $task->progess . '%';?></td>
      <td><?php echo $lang->task->typeList[$task->type];?></td>
      <td>
        <?php common::printIcon('task', 'edit', "taskid=$task->id", '', 'list');?>
        <?php common::printIcon('task', 'delete', "projectID=$task->project&taskid=$task->id", '', 'list', '', 'hiddenwin');?>
      </td>
    </tr>
    <?php $i++;?>
    <?php endforeach;?>
    <?php if($i != 0):?>
    <tr class='actie-disabled group-collapse hidden text-center group-title'>
      <td class='text-left'>
        <?php echo html::a('###', "<i class='icon-caret-right'></i> " . $groupName, '', "class='collapseGroup' data-action='collapse' title='$groupName'");?>
      </td>
      <td></td>
      <td></td>
      <td colspan='11' class='text-left'>
        <span class='groupdivider' style='margin-left:10px;'>
          <span class='text'>
            <?php if($groupBy == 'assignedTo' and isset($members[$task->assignedTo])) printf($lang->project->memberHours, $users[$task->assignedTo], $members[$task->assignedTo]->totalHours);?>
            <?php printf($lang->project->groupSummary, $groupSum, $groupWait, $groupDoing, $groupEstimate, $groupConsumed, $groupLeft);?>
          </span>
        </span>
      </td>
    </tr>
    <?php endif;?>
  </tbody>
  <?php endforeach;?>
</table>
<script language='Javascript'>$('#<?php echo $browseType;?>Tab').addClass('active');</script>
<?php include '../../common/view/footer.html.php';?>
