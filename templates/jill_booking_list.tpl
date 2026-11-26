<{$toolbar|default:''}>
<div class="jb-wrap">
  <h3 class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_SMNAME3}></h3>
  <{if $now_op|default:''=='jill_booking_list'}>
    <select id="jbi_sn" name="jbi_sn" class="form-control form-select mb-3 w-auto" onchange="location.href='<{$action|default:''}>?jbi_sn='+this.value" aria-label="<{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}>">
      <option value=""><{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}></option>
      <{$item_opt|default:''}>
    </select>

    <{if $all_content|default:false}>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <caption class="visually-hidden"><{$smarty.const._MD_JILLBOOKIN_SMNAME3}></caption>
          <thead class="jb-table-head">
            <tr>
              <th scope="col"><{$smarty.const._MD_JILLBOOKIN_ITEM}></th>
              <th scope="col"><{$smarty.const._MD_JILLBOOKIN_JB_UID}></th>
              <th scope="col"><{$smarty.const._MD_JILLBOOKIN_JB_BOOKING_TIME}></th>
              <th scope="col"><{$smarty.const._MD_WAITING}></th>
              <th scope="col"><{$smarty.const._MD_JILLBOOKIN_CONTENT}></th>
              <th scope="col"><{$smarty.const._MD_JILLBOOKIN_JB_STATUS}></th>
              <th scope="col"><{$smarty.const._TAD_FUNCTION}></th>
            </tr>
          </thead>
          <tbody id="jill_booking_sort">
            <{foreach from=$all_content item=data}>
              <tr id="tr_<{$data.jb_sn}>">
                <td><{$data.jbi_title}></td>
                <td><{$data.jb_uid}></td>
                <td><{$data.jb_date}><{$data.jbt_title}></td>
                <td class="text-center"><{$data.jb_waiting}></td>
                <td><{$data.jb_booking_content}></td>
                <td class="text-center">
                  <{if $data.jb_status|default:false}>
                    <span class="jb-text-success fw-medium">
                      <i class="fa-solid fa-check me-1" aria-hidden="true"></i><{$smarty.const._YES}>
                    </span>
                  <{else}>
                    <span class="jb-text-muted">
                      <i class="fa-regular fa-clock me-1" aria-hidden="true"></i><{$smarty.const._NO}>
                    </span>
                  <{/if}>
                </td>
                <td class="text-center">
                  <{if $data.fun|default:false}>
                    <button type="button" onclick="delete_jill_booking_func('<{$data.primary}>');" class="jb-action jb-action-danger">
                      <i class="fa-regular fa-trash-can" aria-hidden="true"></i> <{$smarty.const._TAD_DEL}>
                    </button>
                  <{/if}>
                </td>
              </tr>
            <{/foreach}>
          </tbody>
        </table>
      </div>

      <div class="mt-3"><{$bar|default:''}></div>
    <{else}>
      <div class="alert alert-info d-flex align-items-center justify-content-center gap-2">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <span><{$smarty.const._MD_NO_RECORD}></span>
      </div>
    <{/if}>
  <{/if}>
</div>
