<div class="jb-wrap">
  <{if $now_op|default:''=='list_jill_booking_time'}>
    <{$formValidator_code|default:''}>
    <{$jeditable_set|default:''}>
    <script type="text/javascript">
      $(document).ready(function(){
        $('#sort').sortable({ opacity: 0.6, cursor: 'move', update: function() {
          var order = $(this).sortable('serialize');
          $.post('save_sort.php', order + '&XOOPS_TOKEN_REQUEST=<{$token|default:''}>', function(theResponse){
            $('#save_msg').attr('class', 'alert alert-success mb-3').html(theResponse);
          }).fail(function(xhr) {
            $('#save_msg').attr('class', 'alert alert-danger mb-3').text(xhr.responseText);
          });
        }});
      });
      function change_enable(jbt_sn,w){
        $.post("time.php", {op: 'change_enable', jbt_sn: jbt_sn, week: w, XOOPS_TOKEN_REQUEST: '<{$token|default:''}>' },
        function(data) {
          var btn = $('#'+jbt_sn+'_'+w);
          var isYes = data.indexOf('yes') > -1;
          btn.attr('aria-pressed', isYes ? 'true' : 'false');
          var icon = btn.find('i');
          if (isYes) {
            icon.removeClass('fa-xmark jb-text-muted').addClass('fa-check jb-text-success');
            btn.find('.visually-hidden').text('<{$smarty.const._YES}>');
          } else {
            icon.removeClass('fa-check jb-text-success').addClass('fa-xmark jb-text-muted');
            btn.find('.visually-hidden').text('<{$smarty.const._NO}>');
          }
        }).fail(function(xhr) {
          $('#save_msg').attr('class', 'alert alert-danger mb-3').text(xhr.responseText);
        });
      }
    </script>

    <h3 class="mb-3"><{$item.jbi_title}> - <{$smarty.const._MA_JILLBOOKIN_SETTIME}></h3>

    <div id="save_msg" role="status" aria-live="polite"></div>

    <div class="row g-4">
      <div class="col-lg-8">
        <form action="time.php" method="post" id="myForm">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <caption class="visually-hidden"><{$item.jbi_title}> - <{$smarty.const._MA_JILLBOOKIN_SETTIME}></caption>
              <thead class="jb-table-head">
                <tr>
                  <th scope="col" rowspan="2" class="text-center align-middle"><{$smarty.const._MA_JILLBOOKIN_JBT_TITLE}></th>
                  <th scope="colgroup" colspan="7" class="text-center"><{$smarty.const._MA_JILLBOOKIN_JBT_WEEK}></th>
                  <th scope="col" rowspan="2" class="text-center align-middle"><{$smarty.const._TAD_FUNCTION}></th>
                </tr>
                <tr class="jb-table-subhead">
                  <{foreach from=$weeks key=w item=week}>
                    <th scope="col"><{$week}></th>
                  <{/foreach}>
                </tr>
              </thead>
              <tbody id="sort">
                <{foreach from=$all_content item=data}>
                  <tr id="tr_<{$data.jbt_sn}>">
                    <td class="text-center">
                      <div id="jbt_title_<{$data.jbt_sn}>" class="edit fw-semibold"><{$data.jbt_title}></div>
                    </td>
                    <{foreach from=$weeks key=w item=week}>
                      <td class="text-center p-1">
                        <button type="button" id="<{$data.jbt_sn}>_<{$w}>" class="jb-action p-1" onclick="change_enable(<{$data.jbt_sn}>,<{$w}>);" aria-pressed="<{if $data.open.$w}>true<{else}>false<{/if}>" aria-label="<{$data.jbt_title}> <{$week}>">
                          <i class="fa-solid <{if $data.open.$w}>fa-check jb-text-success<{else}>fa-xmark jb-text-muted<{/if}> fa-lg" aria-hidden="true"></i>
                          <span class="visually-hidden"><{if $data.open.$w}><{$smarty.const._YES}><{else}><{$smarty.const._NO}><{/if}></span>
                        </button>
                      </td>
                    <{/foreach}>
                    <td class="text-center">
                      <div class="d-flex flex-wrap align-items-center justify-content-center gap-1">
                        <span class="jb-sort-handle" title="<{$smarty.const._TAD_SORTABLE}>" aria-label="<{$smarty.const._TAD_SORTABLE}>">
                          <i class="fa-solid fa-up-down" aria-hidden="true"></i>
                        </span>
                        <{if $data.booking_times!=""}>
                          <span class="small jb-text-muted"><{$data.booking_times}></span>
                        <{else}>
                          <button type="button" onclick="delete_jill_booking_time_func(<{$data.jbt_sn}>);" class="jb-action jb-action-danger">
                            <i class="fa-regular fa-trash-can" aria-hidden="true"></i> <{$smarty.const._TAD_DEL}>
                          </button>
                        <{/if}>
                      </div>
                    </td>
                  </tr>
                <{/foreach}>
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td>
                    <input type="text" name="jbt_title" id="jbt_title" class="form-control validate[required]" placeholder="<{$smarty.const._MA_JILLBOOKIN_JBT_TITLE}>" aria-label="<{$smarty.const._MA_JILLBOOKIN_JBT_TITLE}>">
                  </td>
                  <{foreach from=$weeks key=w item=week}>
                    <td class="text-center align-middle p-1">
                      <label class="jb-check-label">
                        <input type="checkbox" class="form-check-input m-0" name="jbt_week[]" value="<{$w}>" aria-label="<{$week}>" <{if $w>0 && $w<6}>checked<{/if}>>
                      </label>
                    </td>
                  <{/foreach}>
                  <td class="text-center">
                    <input type="hidden" name="jbi_sn" value="<{$jbi_sn|default:''}>">
                    <input type="hidden" name="XOOPS_TOKEN_REQUEST" value="<{$token|default:''}>">
                    <input type="hidden" name="op" value="insert_jill_booking_time">
                    <button type="submit" class="btn btn-primary">
                      <i class="fa-solid fa-plus" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_ADD_TIME}>
                    </button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </form>
      </div>

      <div class="col-lg-4">
        <{if !$all_content}>
          <div class="border rounded bg-white overflow-hidden">
            <div class="px-3 py-2 fw-semibold border-bottom" style="background-color:#EEF4F8;color:#1E293B;">
              <i class="fa-solid fa-file-import me-1" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_IMPORT}>
            </div>
            <div class="list-group list-group-flush">
              <a href="time.php?op=import_time&jbi_sn=<{$jbi_sn|default:''}>&type=18&XOOPS_TOKEN_REQUEST=<{$token|default:''}>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-import" aria-hidden="true"></i>
                <span><{$smarty.const._MA_JILLBOOKIN_IMPORT_18}></span>
              </a>
              <a href="time.php?op=import_time&jbi_sn=<{$jbi_sn|default:''}>&type=apm&XOOPS_TOKEN_REQUEST=<{$token|default:''}>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-import" aria-hidden="true"></i>
                <span><{$smarty.const._MA_JILLBOOKIN_IMPORT_APM}></span>
              </a>
              <{foreach from=$place_time item=place}>
                <a href="time.php?op=copy_time&jbi_sn=<{$place.jbi_sn}>&to_jbi_sn=<{$jbi_sn|default:''}>&XOOPS_TOKEN_REQUEST=<{$token|default:''}>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                  <i class="fa-regular fa-copy" aria-hidden="true"></i>
                  <span><{$place.jbi_link}></span>
                </a>
              <{/foreach}>
            </div>
          </div>
        <{/if}>
      </div>
    </div>
  <{/if}>
</div>
