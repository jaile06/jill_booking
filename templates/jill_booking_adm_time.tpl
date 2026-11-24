<{if $now_op=="list_jill_booking_time"}>
  <{$formValidator_code|default:''}>
  <{$jeditable_set|default:''}>
  <script type="text/javascript">
    $(document).ready(function(){
      $('#sort').sortable({ opacity: 0.6, cursor: 'move', update: function() {
        var order = $(this).sortable('serialize');
        $.post('save_sort.php', order + '&XOOPS_TOKEN_REQUEST=<{$token|default:''}>', function(theResponse){
          $('#save_msg').html(theResponse);
        }).fail(function(xhr) {
          $('#save_msg').text(xhr.responseText);
        });
      }
      });
    });
    function change_enable(jbt_sn,w){
      $.post("time.php", {op: 'change_enable', jbt_sn: jbt_sn, week: w, XOOPS_TOKEN_REQUEST: '<{$token|default:''}>' },
      function(data) {
        var btn = $('#'+jbt_sn+'_'+w);
        btn.attr('aria-pressed', data.indexOf('yes') > -1);
        btn.find('img').attr('src', data);
      }).fail(function(xhr) {
        $('#save_msg').text(xhr.responseText);
      });
    }
  </script>
  <h3><{$item.jbi_title}><{$smarty.const._MA_JILLBOOKIN_SETTIME}></h3>

  <div id="save_msg" role="status"></div>
  <div class="row">
    <div class="col-sm-7">
      <form action="time.php" method="post" id="myForm">
        <table class="table table-striped table-hover">
          <thead>
            <tr class="bg-secondary">
              <th scope="col" rowspan="2" class="text-center align-middle"><{$smarty.const._MA_JILLBOOKIN_JBT_TITLE}></th>
              <th scope="colgroup" colspan="7" class="text-center"><{$smarty.const._MA_JILLBOOKIN_JBT_WEEK}></th>
              <th scope="col" rowspan="2" class="text-center align-middle"><{$smarty.const._TAD_FUNCTION}></th>
            </tr>
            <tr>
              <{foreach from=$weeks key=w item=week}>
                <th scope="col" style="background-color: #4E525B;"><{$week}></th>
              <{/foreach}>
            </tr>
          </thead>

          <tbody id="sort">
            <{foreach from=$all_content item=data}>
              <tr id="tr_<{$data.jbt_sn}>">
                <td class="text-center">
                  <div id="jbt_title_<{$data.jbt_sn}>"><{$data.jbt_title}></div>
                </td>
                <{foreach from=$weeks key=w item=week}>
                  <td class="text-center">
                    <button type="button" id="<{$data.jbt_sn}>_<{$w}>" class="btn btn-link p-1" onclick="change_enable(<{$data.jbt_sn}>,<{$w}>);" aria-pressed="<{if $data.open.$w}>true<{else}>false<{/if}>" aria-label="<{$data.jbt_title}> <{$week}>">
                      <img src="../images/<{if $data.open.$w}>yes<{else}>no<{/if}>.gif" alt="">
                    </button>
                  </td>
                <{/foreach}>
                <td class="text-center">
                  <img src="<{$xoops_url}>/modules/tadtools/treeTable/images/updown_s.png" style="cursor: s-resize;margin:0px 4px;" alt="<{$smarty.const._TAD_SORTABLE}>" title="<{$smarty.const._TAD_SORTABLE}>">
                  <{if $data.booking_times!=""}>
                    <{$data.booking_times}>
                  <{else}>
                    <a href="javascript:delete_jill_booking_time_func(<{$data.jbt_sn}>);" class="btn btn-sm btn-danger"><i class="fa fa-times" aria-hidden="true"></i> <{$smarty.const._TAD_DEL}></a>
                  <{/if}>
                </td>
              </tr>
            <{/foreach}>
          </tbody>
          <tr>
            <td>
              <input type="text" name="jbt_title" id="jbt_title" class="form-control validate[required]" placeholder="<{$smarty.const._MA_JILLBOOKIN_JBT_TITLE}>" aria-label="<{$smarty.const._MA_JILLBOOKIN_JBT_TITLE}>">
            </td>
            <{foreach from=$weeks key=w item=week}>
              <td class="text-center">
                <input type="checkbox" name="jbt_week[]" value="<{$w}>" aria-label="<{$week}>" <{if $w>0 && $w<6}>checked<{/if}>>
              </td>
            <{/foreach}>
            <td>
              <input type="hidden" name="jbi_sn" value="<{$jbi_sn|default:''}>">
              <input type="hidden" name="XOOPS_TOKEN_REQUEST" value="<{$token|default:''}>">
              <input type="hidden" name="op" value="insert_jill_booking_time">
              <button type="submit" class="btn btn-outline-primary"><{$smarty.const._MA_JILLBOOKIN_ADD_TIME}></button>
            </td>
          </tr>
        </table>
      </form>
    </div>
    <div class="col-sm-5">
      <{if !$all_content}>
        <div class="list-group">
          <div class="list-group-item active"><{$smarty.const._MA_JILLBOOKIN_IMPORT}></div>
          <a href="time.php?op=import_time&jbi_sn=<{$jbi_sn|default:''}>&type=18&XOOPS_TOKEN_REQUEST=<{$token|default:''}>" class="list-group-item"><{$smarty.const._MA_JILLBOOKIN_IMPORT_18}></a>
          <a href="time.php?op=import_time&jbi_sn=<{$jbi_sn|default:''}>&type=apm&XOOPS_TOKEN_REQUEST=<{$token|default:''}>" class="list-group-item"><{$smarty.const._MA_JILLBOOKIN_IMPORT_APM}></a>
          <{foreach from=$place_time item=place}>
            <a href="time.php?op=copy_time&jbi_sn=<{$place.jbi_sn}>&to_jbi_sn=<{$jbi_sn|default:''}>&XOOPS_TOKEN_REQUEST=<{$token|default:''}>" class="list-group-item"><{$place.jbi_link}></a>
          <{/foreach}>
        </div>
      <{/if}>
    </div>
  </div>
<{/if}>
