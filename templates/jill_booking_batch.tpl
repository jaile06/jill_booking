<{$toolbar|default:''}>
<div class="jb-wrap">
  <h3 class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_SMNAME2}></h3>
  <{if $smarty.session.can_booking|default:false}>
    <{include file="$xoops_rootpath/modules/jill_booking/templates/op_`$now_op`.tpl"}>
  <{else}>
    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
      <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
      <span><{$smarty.const._MD_JBOOKING_ERROR}></span>
    </div>
  <{/if}>
</div>
