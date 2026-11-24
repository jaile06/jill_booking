<{$toolbar|default:''}>
<{$jquery|default:''}>
<{if $now_op=="booking_table"}>
  <script type="text/javascript">
    function single_insert_booking(t,wk,jbt_sn,jb_date,jbi_sn){
      $.post('<{$action|default:''}>', {op: 'single_insert_booking', jbt_sn: jbt_sn, jb_date: jb_date, jbi_sn: jbi_sn, XOOPS_TOKEN_REQUEST: '<{$token|default:''}>' },
      function(data) {
        $('#submit'+t+'_'+wk).html(data).css('color','#000000');
      }).fail(function(xhr) {
        $('#submit'+t+'_'+wk).text(xhr.responseText).css('color','#B3261E');
      });
    }
  </script>

  <h3 class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_SMNAME1}></h3>

  <select name="jbi_sn" id="jbi_sn" class="form-control form-select mb-3 w-auto" onchange="location.href='<{$action|default:''}>?jbi_sn='+this.value" aria-label="<{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}>">
    <option value=""><{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}></option>
    <{$item_opt|default:''}>
  </select>

  <{if $jbi_sn|default:false}>
    <div class="row align-items-center mb-2">
      <div class="col-sm-4">
        <a class="btn btn-link" href="<{$action|default:''}>?op=booking_table&jbi_sn=<{$jbi_sn}>&getdate=<{$prev_date}>"><i class="fa fa-long-arrow-left" aria-hidden="true"></i> <{$smarty.const._MD_LASTWEEK}></a>
      </div>
      <div class="col-sm-4 text-center">
        <span class="fs-4"><{$itemArr.jbi_title}></span>
        <{if $itemArr.jbi_desc|default:false}>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#jb_desc" aria-label="<{$smarty.const._MD_JILLBOOKIN_DESC}>"><i class="fa fa-comment-dots" aria-hidden="true"></i></button>
        <{/if}>
      </div>
      <div class="col-sm-4 text-end">
        <a class="btn btn-link" href="<{$action|default:''}>?op=booking_table&jbi_sn=<{$jbi_sn}>&getdate=<{$next_date}>"><{$smarty.const._MD_NEXTWEEK}> <i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
      </div>
    </div>
    <{if $itemArr.jbi_desc|default:false}>
      <div class="modal fade" id="jb_desc" tabindex="-1" aria-label="<{$smarty.const._MD_JILLBOOKIN_DESC}>" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content text-start p-4"><{$itemArr.jbi_desc}></div>
        </div>
      </div>
    <{/if}>

    <div class="table-responsive">
      <table class="table table-striped table-hover table-bordered">
        <thead>
          <tr>
            <th scope="col" class="th_style"><span class="visually-hidden"><{$smarty.const._MD_JSESSION}></span></th>
            <{foreach from=$weekArr item=week}>
              <th scope="col" class="th_style">
                <div><{$week.y}></div>
                <div><{$week.md}></div>
                <div>(<{$week.w}>)</div>
              </th>
            <{/foreach}>
          </tr>
        </thead>
        <tbody>
          <{foreach from=$timeArr key=t item=time}>
            <tr>
              <th scope="row" class="text-center align-middle fw-normal"><{$time.jbt_title}></th>
              <{foreach from=$weekArr key=wk item=week}>
                <td>
                  <div id="submit<{$t}>_<{$wk}>" class="text-center align-middle" style="color:<{$bookingArr.$t.$wk.color}>;">
                    <{if $bookingArr.$t.$wk.status}>
                      <button type="button" class="btn btn-info" aria-label="<{$smarty.const._MD_SINGLE_SAVE}> <{$week.d}> <{$time.jbt_title}>" onclick="<{$bookingArr.$t.$wk.status.func}>('<{$t}>' , '<{$wk}>' , '<{$bookingArr.$t.$wk.status.time}>','<{$bookingArr.$t.$wk.status.weekinfo}>','<{$bookingArr.$t.$wk.status.jbi_sn}>')"><i class="fa fa-pencil" aria-hidden="true"></i></button>
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
    <div class="alert alert-info">
      <ul class="m-0">
        <li><{$smarty.const._MD_JILLBOOKIN_CHOOSEITEMINFO}></li>
        <li><{$smarty.const._MD_JILLBOOKIN_PLEASE_LOGIN}></li>
      </ul>
    </div>
  <{/if}>
<{/if}>
