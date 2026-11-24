<h4 class="mb-3"><{$jbi_title|default:''}></h4>
<form action="<{$action|default:''}>" method="post" id="myForm">
  <div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center mb-0" id="jb_batch">
      <caption class="visually-hidden"><{$jbi_title|default:''}></caption>
      <thead class="jb-table-head">
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
            <td class="p-1">
              <label class="jb-check-label">
                <input type="checkbox" class="form-check-input m-0" name="jb_date[<{$dw.jbt_sn}>][]" value="<{$dw.jb_date}>" aria-label="<{$dw.jb_date}> <{$dw.jbt_title}>" <{if !$dw.jb_exit}>checked<{/if}> <{if $dw.own}>disabled title="<{$smarty.const._MD_JILLBOOKIN_BOOKED_SELF}>"<{/if}>>
              </label>
            </td>
            <td><{$dw.jb_date}></td>
            <td><{$dw.week}></td>
            <td><{$dw.jbt_title}></td>
            <{foreach from=$dw.waitingArr item=w}>
              <td>
                <{if $w.free}>
                  <span class="jb-text-success"><i class="fa-solid fa-check" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_NO}></span></span>
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
  <div class="text-center mt-4">
    <{$token_form|default:''}>
    <input type="hidden" name="op" value="<{$next_op|default:''}>">
    <input type="hidden" name="key" value="<{$batch_key|default:''}>">
    <button type="submit" class="btn btn-primary px-4">
      <{$smarty.const._TAD_SAVE}>
    </button>
  </div>
</form>
