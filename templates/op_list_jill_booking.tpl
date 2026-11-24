<h3><{$jbi_title|default:''}></h3>
<form action="<{$action|default:''}>" method="post" id="myForm">
  <div class="table-responsive">
    <table class="table table-bordered table-sm text-center" id="jb_batch">
      <thead>
        <tr>
          <th scope="col" class="th_style"><{$smarty.const._MD_JBOOKING}></th>
          <th scope="col" class="th_style"><{$smarty.const._MD_JDATE}></th>
          <th scope="col" class="th_style"><{$smarty.const._MD_JWEEK}></th>
          <th scope="col" class="th_style"><{$smarty.const._MD_JSESSION}></th>
          <{foreach from=$waitings item=n}>
            <th scope="col" class="th_style"><{$smarty.const._MD_JILLBOOKIN_JB_UID}><{$n}></th>
          <{/foreach}>
        </tr>
      </thead>
      <tbody>
        <{foreach from=$dateweek item=dw}>
          <tr>
            <td>
              <input type="checkbox" name="jb_date[<{$dw.jbt_sn}>][]" value="<{$dw.jb_date}>" aria-label="<{$dw.jb_date}> <{$dw.jbt_title}>" <{if !$dw.jb_exit}>checked<{/if}> <{if $dw.own}>disabled title="<{$smarty.const._MD_JILLBOOKIN_BOOKED_SELF}>"<{/if}>>
            </td>
            <td><{$dw.jb_date}></td>
            <td><{$dw.week}></td>
            <td><{$dw.jbt_title}></td>
            <{foreach from=$dw.waitingArr item=w}>
              <td>
                <{if $w.free}>
                  <i class="fa fa-check jb_closed" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_NO}></span>
                <{else}>
                  <{$w.name}>
                <{/if}>
              </td>
            <{/foreach}>
          </tr>
        <{/foreach}>
      </tbody>
    </table>
  </div>
  <div class="text-center">
    <{$token_form|default:''}>
    <input type="hidden" name="op" value="<{$next_op|default:''}>">
    <input type="hidden" name="key" value="<{$batch_key|default:''}>">
    <button type="submit" class="btn btn-primary"><{$smarty.const._TAD_SAVE}></button>
  </div>
</form>
