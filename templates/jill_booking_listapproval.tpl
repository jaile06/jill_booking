<{$toolbar|default:''}>
<div class="jb-wrap">
  <h3 class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_SMNAME4}></h3>

  <{if $now_op=="jill_booking_approvallist"}>
    <script type='text/javascript'>
      function update_jb_status_func(jb_sn,jb_date,jbt_sn,jbi_sn,i){
        $.post("listapproval.php", {op: 'update_jb_status', jb_sn: jb_sn, jb_date: jb_date,jbt_sn: jbt_sn, jbi_sn: jbi_sn, XOOPS_TOKEN_REQUEST: '<{$token|default:''}>' },
        function(data) {
          if(data=='1'){
            $('#pass').attr('class','my-3 alert alert-success');
            $('#pass').text('<{$smarty.const._MD_UPDATE_COMPLETED}>');
            $('.'+jb_date+'_'+jbt_sn).remove();
          }
          else{
            $('#pass').attr('class','my-3 alert alert-danger');
            $('#pass').text('<{$smarty.const._MD_UPDATE_FAILED}>');
          }
        }).fail(function(xhr) {
          $('#pass').attr('class','my-3 alert alert-danger').text(xhr.responseText);
        });
      }
    </script>

    <select name="jbi_sn" id="jbi_sn" class="form-control form-select mb-3 w-auto" onchange="location.href='<{$action|default:''}>?jbi_sn='+this.value" aria-label="<{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}>">
      <option value=""><{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}></option>
      <{$item_opt|default:''}>
    </select>

    <{if $all_content|default:false}>
      <div id="pass" class="my-3" role="status" aria-live="polite"></div>
      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle mb-0">
          <caption class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_SMNAME4}></caption>
          <thead class="jb-table-head">
            <tr>
              <th scope="col"><{$smarty.const._MD_JILLBOOKIN_JB_UID}></th>
              <th scope="col"><{$smarty.const._MD_JILLBOOKIN_JB_BOOKING_TIME}></th>
              <th scope="col"><{$smarty.const._MD_JWEEK}> / <{$smarty.const._MD_JSESSION}></th>
              <th scope="col"><{$smarty.const._MD_WAITING}></th>
              <th scope="col"><{$smarty.const._MD_JILLBOOKIN_CONTENT}></th>
              <th scope="col"><{$smarty.const._TAD_FUNCTION}></th>
            </tr>
          </thead>
          <tbody id="jill_booking_sort">
            <{foreach from=$all_content key=i item=data}>
              <tr id="tr_<{$i|default:''}>" class="<{$data.jb_date}>_<{$data.jbt_sn}>">
                <td><{$data.jb_uid}></td>
                <td>
                  <div><{$data.jb_date}></div>
                  <{if $data.had_pass|default:false}>
                    <div class="small jb-text-closed"><{$data.had_pass}></div>
                  <{/if}>
                </td>
                <td>
                  <{if $data.jb_week=="0"}><{$smarty.const._MD_JILLBOOKIN_W0}> / <{$data.jbt_title}>
                  <{elseif $data.jb_week=="1"}><{$smarty.const._MD_JILLBOOKIN_W1}> / <{$data.jbt_title}>
                  <{elseif $data.jb_week=="2"}><{$smarty.const._MD_JILLBOOKIN_W2}> / <{$data.jbt_title}>
                  <{elseif $data.jb_week=="3"}><{$smarty.const._MD_JILLBOOKIN_W3}> / <{$data.jbt_title}>
                  <{elseif $data.jb_week=="4"}><{$smarty.const._MD_JILLBOOKIN_W4}> / <{$data.jbt_title}>
                  <{elseif $data.jb_week=="5"}><{$smarty.const._MD_JILLBOOKIN_W5}> / <{$data.jbt_title}>
                  <{else}><{$smarty.const._MD_JILLBOOKIN_W6}> / <{$data.jbt_title}>
                  <{/if}>
                </td>
                <td class="text-center"><{$data.jb_waiting}></td>
                <td><{$data.jb_booking_content}></td>
                <td>
                  <div class="d-flex flex-wrap justify-content-center gap-1">
                    <{if $data.had_pass|default:'' == ''}>
                      <button type="button" onclick="update_jb_status_func(<{$data.jb_sn}>,'<{$data.jb_date}>',<{$data.jbt_sn}>,<{$data.jbi_sn}>,<{$i|default:''}>);" class="jb-action jb-action-success">
                        <i class="fa-solid fa-check" aria-hidden="true"></i> <{$smarty.const._MD_PASS}>
                      </button>
                    <{/if}>
                    <button type="button" onclick="delete_jill_booking_func('<{$data.jb_sn}>_<{$data.jbt_sn}>_<{$data.jb_date}>_<{$data.jbi_sn}>');" class="jb-action jb-action-danger">
                      <i class="fa-regular fa-trash-can" aria-hidden="true"></i> <{$smarty.const._TAD_DEL}>
                    </button>
                  </div>
                </td>
              </tr>
            <{/foreach}>
          </tbody>
        </table>
      </div>

      <div class="mt-3"><{$bar|default:''}></div>
    <{else}>
      <div class="alert alert-info d-flex align-items-center justify-content-center gap-2 mt-4">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <span><{$smarty.const._MD_NO_RECORD}></span>
      </div>
    <{/if}>
  <{/if}>
</div>
