<div class="border rounded p-3 p-md-4 bg-white">
  <h3 class="h4 border-bottom pb-2 mb-3"><{$jbi_title|default:''}></h3>
  <dl class="row mb-0">
    <{if $jbi_desc|default:false}>
      <dt class="col-sm-3 text-sm-end fw-semibold"><{$smarty.const._MA_JILLBOOKIN_JBI_DESC}></dt>
      <dd class="col-sm-9 mb-3">
        <div class="border rounded p-3 bg-light">
          <{$jbi_desc|default:''}>
        </div>
      </dd>
    <{/if}>

    <dt class="col-sm-3 text-sm-end fw-semibold"><{$smarty.const._MA_JILLBOOKIN_JBI_START}></dt>
    <dd class="col-sm-9 mb-3"><{$jbi_start|default:''}></dd>

    <dt class="col-sm-3 text-sm-end fw-semibold"><{$smarty.const._MA_JILLBOOKIN_JBI_END}></dt>
    <dd class="col-sm-9 mb-3"><{$jbi_end|default:''}></dd>

    <dt class="col-sm-3 text-sm-end fw-semibold"><{$smarty.const._MA_JILLBOOKIN_JBI_ENABLE}></dt>
    <dd class="col-sm-9 mb-3"><{$jbi_enable|default:''}></dd>

    <dt class="col-sm-3 text-sm-end fw-semibold"><{$smarty.const._MA_JILLBOOKIN_JBI_APPROVAL}></dt>
    <dd class="col-sm-9 mb-0"><{$jbi_approval|default:''}></dd>
  </dl>

  <div class="d-flex flex-wrap justify-content-end gap-1 border-top pt-3 mt-4">
    <{if $jill_book_adm|default:false}>
      <a href="<{$xoops_url}>/modules/jill_booking/admin/main.php?op=jill_booking_item_form&jbi_sn=<{$jbi_sn|default:''}>" class="jb-action">
        <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> <{$smarty.const._TAD_EDIT}>
      </a>
      <a href="<{$xoops_url}>/modules/jill_booking/admin/time.php?op=list_jill_booking_time&jbi_sn=<{$jbi_sn|default:''}>" class="jb-action jb-action-success">
        <i class="fa-regular fa-clock" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_SETTIME}>
      </a>
      <a href="<{$xoops_url}>/modules/jill_booking/admin/approval.php?jbi_sn=<{$jbi_sn|default:''}>" class="jb-action jb-action-primary">
        <i class="fa-solid fa-user-check" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_SETAPPROVAL}>
      </a>
      <a href="<{$xoops_url}>/modules/jill_booking/admin/main.php?op=jill_booking_item_form" class="jb-action jb-action-primary">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_ADD}>
      </a>
      <button type="button" onclick="delete_jill_booking_item_func(<{$jbi_sn|default:''}>);" class="jb-action jb-action-danger">
        <i class="fa-regular fa-trash-can" aria-hidden="true"></i> <{$smarty.const._TAD_DEL}>
      </button>
    <{/if}>
    <a href="<{$action|default:''}>" class="jb-action">
      <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> <{$smarty.const._MA_JILLBOOKIN_BACK}>
    </a>
  </div>
</div>
