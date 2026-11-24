<{if $orphans|default:false}>
  <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
    <i class="fa-solid fa-triangle-exclamation mt-1" aria-hidden="true"></i>
    <div>
      <div class="fw-semibold"><{$smarty.const._MA_JILLBOOKIN_ORPHAN_TITLE}></div>
      <ul class="mb-0 mt-1">
        <{foreach from=$orphans key=label item=count}>
          <li><{$label}>: <{$count}></li>
        <{/foreach}>
      </ul>
    </div>
  </div>
<{/if}>

<{if $all_content|default:false}>
  <{if $jill_book_adm|default:false}>
    <script type="text/javascript">
      $(document).ready(function(){
        $("#jill_booking_item_sort").sortable({ opacity: 0.6, cursor: "move", update: function() {
          var order = $(this).sortable("serialize");
          $.post("<{$xoops_url}>/modules/jill_booking/admin/main.php", order + "&op=update_jill_booking_item_sort&XOOPS_TOKEN_REQUEST=<{$token|default:''}>", function(theResponse){
            $("#jill_booking_item_save_msg").attr("class", "alert alert-success").html(theResponse);
          });
        }});
      });
    </script>
  <{/if}>

  <div id="jill_booking_item_save_msg" role="status" aria-live="polite"></div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <caption class="visually-hidden"><{$smarty.const._MA_JILLBOOKIN_JBI_TITLE}></caption>
      <thead class="jb-table-head">
        <tr>
          <th scope="col"><{$smarty.const._MA_JILLBOOKIN_JBI_TITLE}></th>
          <th scope="col"><{$smarty.const._MA_JILLBOOKIN_JBI_PERIOD}></th>
          <th scope="col"><{$smarty.const._MA_JILLBOOKIN_JBI_ENABLE}></th>
          <th scope="col"><{$smarty.const._MA_JILLBOOKIN_JBI_APPROVAL}></th>
          <{if $jill_book_adm|default:false}>
            <th scope="col"><{$smarty.const._TAD_FUNCTION}></th>
          <{/if}>
        </tr>
      </thead>
      <tbody id="jill_booking_item_sort">
        <{foreach from=$all_content item=data}>
          <tr id="tr_<{$data.jbi_sn}>">
            <td>
              <a href="time.php?jbi_sn=<{$data.jbi_sn}>" class="fw-semibold text-decoration-none"><{$data.jbi_title}></a>
            </td>
            <td><{$data.jbi_start}> ~ <{$data.jbi_end}></td>
            <td class="text-center"><{$data.jbi_enable}></td>
            <td><{$data.jbi_approval}></td>
            <{if $jill_book_adm|default:false}>
              <td>
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-1">
                  <a href="<{$xoops_url}>/modules/jill_booking/admin/main.php?op=jill_booking_item_form&jbi_sn=<{$data.jbi_sn}>" class="jb-action">
                    <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> <{$smarty.const._TAD_EDIT}>
                  </a>
                  <a href="<{$xoops_url}>/modules/jill_booking/admin/time.php?op=list_jill_booking_time&jbi_sn=<{$data.jbi_sn}>" class="jb-action jb-action-success">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_SETTIME}>
                  </a>
                  <a href="<{$xoops_url}>/modules/jill_booking/admin/approval.php?jbi_sn=<{$data.jbi_sn}>" class="jb-action jb-action-primary">
                    <i class="fa-solid fa-user-check" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_SETAPPROVAL}>
                  </a>
                  <button type="button" onclick="delete_jill_booking_item_func(<{$data.jbi_sn}>);" class="jb-action jb-action-danger">
                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i> <{$smarty.const._TAD_DEL}>
                  </button>
                  <span class="jb-sort-handle" title="<{$smarty.const._TAD_SORTABLE}>" aria-label="<{$smarty.const._TAD_SORTABLE}>">
                    <i class="fa-solid fa-up-down" aria-hidden="true"></i>
                  </span>
                </div>
              </td>
            <{/if}>
          </tr>
        <{/foreach}>
      </tbody>
    </table>
  </div>

  <{if $jill_book_adm|default:false}>
    <div class="text-end mt-3">
      <a href="<{$xoops_url}>/modules/jill_booking/admin/main.php?op=jill_booking_item_form" class="btn btn-primary">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_ADD}>
      </a>
    </div>
  <{/if}>

  <div class="mt-3"><{$bar|default:''}></div>
<{else}>
  <{if $jill_book_adm|default:false}>
    <div class="border rounded p-5 text-center bg-light">
      <a href="<{$xoops_url}>/modules/jill_booking/admin/main.php?op=jill_booking_item_form" class="btn btn-primary">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_ADD}>
      </a>
    </div>
  <{/if}>
<{/if}>
