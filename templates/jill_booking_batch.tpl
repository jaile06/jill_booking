<{$toolbar|default:''}>
<h3 class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_SMNAME2}></h3>
<{if $smarty.session.can_booking|default:false}>
  <{include file="$xoops_rootpath/modules/jill_booking/templates/op_`$now_op`.tpl"}>
<{else}>
  <p class="alert alert-danger"><{$smarty.const._MD_JBOOKING_ERROR}></p>
<{/if}>
