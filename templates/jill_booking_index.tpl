<{$toolbar|default:''}>
<{$jquery|default:''}>
<div class="jb-wrap">
  <{if $now_op=="booking_table"}>
    <script type="text/javascript">
      function single_insert_booking(t,wk,jbt_sn,jb_date,jbi_sn){
        $.post('<{$action|default:''}>', {op: 'single_insert_booking', jbt_sn: jbt_sn, jb_date: jb_date, jbi_sn: jbi_sn, XOOPS_TOKEN_REQUEST: '<{$token|default:''}>' },
        function(data) {
          $('#submit'+t+'_'+wk).html(data).css('color','#1E293B');
          $('#jb_calendar_status').text('<{$smarty.const._MD_UPDATE_COMPLETED}>');
        }).fail(function(xhr) {
          $('#submit'+t+'_'+wk).text(xhr.responseText).css('color','#9B1C1C');
          $('#jb_calendar_status').text(xhr.responseText);
        });
      }
    </script>

    <h3 class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_SMNAME1}></h3>

    <select name="jbi_sn" id="jbi_sn" class="form-control form-select mb-3 w-auto" onchange="location.href='<{$action|default:''}>?jbi_sn='+this.value" aria-label="<{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}>">
      <option value=""><{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}></option>
      <{$item_opt|default:''}>
    </select>

    <{if $jbi_sn|default:false}>
      <div id="jb_calendar_status" class="visually-hidden" role="status" aria-live="polite"></div>

      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <a class="jb-action jb-action-primary" href="<{$action|default:''}>?op=booking_table&jbi_sn=<{$jbi_sn}>&getdate=<{$prev_date}>">
          <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> <{$smarty.const._MD_LASTWEEK}>
        </a>
        <div class="d-flex align-items-center gap-1">
          <h4 class="mb-0 fw-semibold"><{$itemArr.jbi_title}></h4>
          <{if $itemArr.jbi_desc|default:false}>
            <button type="button" class="jb-action jb-action-primary" data-bs-toggle="modal" data-bs-target="#jb_desc" aria-label="<{$smarty.const._MD_JILLBOOKIN_DESC}>" title="<{$smarty.const._MD_JILLBOOKIN_DESC}>">
              <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            </button>
          <{/if}>
        </div>
        <a class="jb-action jb-action-primary" href="<{$action|default:''}>?op=booking_table&jbi_sn=<{$jbi_sn}>&getdate=<{$next_date}>">
          <{$smarty.const._MD_NEXTWEEK}> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </a>
      </div>

      <{if $itemArr.jbi_desc|default:false}>
        <div class="modal fade" id="jb_desc" tabindex="-1" aria-labelledby="jb_desc_title" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="jb_desc_title"><{$itemArr.jbi_title}> - <{$smarty.const._MD_JILLBOOKIN_DESC}></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<{$smarty.const._CLOSE}>"></button>
              </div>
              <div class="modal-body text-start">
                <{$itemArr.jbi_desc}>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><{$smarty.const._CLOSE}></button>
              </div>
            </div>
          </div>
        </div>
      <{/if}>

      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle mb-0">
          <caption class="visually-hidden"><{$itemArr.jbi_title}> - <{$smarty.const._MD_JILLBOOKIN_SMNAME1}></caption>
          <thead class="jb-table-head">
            <tr>
              <th scope="col" class="th_style"><span class="visually-hidden"><{$smarty.const._MD_JSESSION}></span></th>
              <{foreach from=$weekArr item=week}>
                <th scope="col" class="th_style">
                  <div class="small fw-normal"><{$week.y}></div>
                  <div><{$week.md}></div>
                  <div class="small fw-normal">(<{$week.w}>)</div>
                </th>
              <{/foreach}>
            </tr>
          </thead>
          <tbody>
            <{foreach from=$timeArr key=t item=time}>
              <tr>
                <th scope="row" class="text-center align-middle fw-medium"><{$time.jbt_title}></th>
                <{foreach from=$weekArr key=wk item=week}>
                  <td class="text-center align-middle">
                    <div id="submit<{$t}>_<{$wk}>" style="color:<{$bookingArr.$t.$wk.color}>;">
                      <{if $bookingArr.$t.$wk.status}>
                        <button type="button" class="jb-slot-btn" aria-label="<{$smarty.const._MD_SINGLE_SAVE}> <{$week.d}> <{$time.jbt_title}>" title="<{$smarty.const._MD_SINGLE_SAVE}>" onclick="<{$bookingArr.$t.$wk.status.func}>('<{$t}>' , '<{$wk}>' , '<{$bookingArr.$t.$wk.status.time}>','<{$bookingArr.$t.$wk.status.weekinfo}>','<{$bookingArr.$t.$wk.status.jbi_sn}>')">
                          <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        </button>
                      <{else}>
                        <{$bookingArr.$t.$wk.content}>
                      <{/if}>
                    </div>
                  </td>
                <{/foreach}>
              </tr>
            <{/foreach}>
          </tbody>
        </table>
      </div>
    <{else}>
      <div class="alert alert-info d-flex align-items-start gap-2" role="region" aria-label="<{$smarty.const._MD_JILLBOOKIN_SMNAME1}>">
        <i class="fa-solid fa-circle-info mt-1" aria-hidden="true"></i>
        <ul class="mb-0 ps-3">
          <li><{$smarty.const._MD_JILLBOOKIN_CHOOSEITEMINFO}></li>
          <li><{$smarty.const._MD_JILLBOOKIN_PLEASE_LOGIN}></li>
        </ul>
      </div>
    <{/if}>
  <{/if}>
</div>
