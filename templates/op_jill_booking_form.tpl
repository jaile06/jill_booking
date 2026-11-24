<select name="jbi_sn" id="jbi_sn" class="form-control form-select mb-3 w-auto" onchange="location.href='<{$action|default:''}>?jbi_sn='+this.value" aria-label="<{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}>">
  <option value=""><{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}></option>
  <{$item_opt|default:''}>
</select>

<{if $itemArr.jbi_sn|default:false}>
  <{$formValidator_code|default:''}>
  <div class="border rounded p-3 p-md-4 bg-white">
    <h4 class="border-bottom pb-2 mb-3"><{$itemArr.jbi_title}></h4>
    <form action="<{$action|default:''}>" method="post" id="myForm">
      <div class="row mb-3 align-items-center">
        <label class="col-md-2 col-form-label text-md-end fw-semibold" for="jb_start_date"><{$smarty.const._MD_JILLBOOKIN_JB_DATE}></label>
        <div class="col-md-10">
          <div class="row g-2 align-items-center">
            <div class="col-sm-5">
              <div class="input-group">
                <input type="text" name="jb_start_date" id="jb_start_date" class="form-control" value="<{$jb_start_date|default:''}>" onclick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d', minDate:'<{$jb_start_date|default:''}>'<{if $max_date|default:false}>, maxDate:'<{$max_date}>'<{/if}>})" placeholder="<{$smarty.const._MD_JILLBOOKIN_JB_START_DATE}>" aria-label="<{$smarty.const._MD_JILLBOOKIN_JB_START_DATE}>">
                <button type="button" class="btn btn-outline-secondary" onclick="WdatePicker({el:'jb_start_date', dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d', minDate:'<{$jb_start_date|default:''}>'<{if $max_date|default:false}>, maxDate:'<{$max_date}>'<{/if}>})" aria-label="<{$smarty.const._MD_JILLBOOKIN_JB_START_DATE}>">
                  <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="col-sm-2 text-center">
              <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </div>
            <div class="col-sm-5">
              <div class="input-group">
                <input type="text" name="jb_end_date" id="jb_end_date" class="form-control" value="<{$jb_end_date|default:''}>" onclick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d', minDate:'<{$jb_end_date|default:''}>'<{if $max_date|default:false}>, maxDate:'<{$max_date}>'<{/if}>})" placeholder="<{$smarty.const._MD_JILLBOOKIN_JB_END_DATE}>" aria-label="<{$smarty.const._MD_JILLBOOKIN_JB_END_DATE}>">
                <button type="button" class="btn btn-outline-secondary" onclick="WdatePicker({el:'jb_end_date', dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d', minDate:'<{$jb_end_date|default:''}>'<{if $max_date|default:false}>, maxDate:'<{$max_date}>'<{/if}>})" aria-label="<{$smarty.const._MD_JILLBOOKIN_JB_END_DATE}>">
                  <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-3 align-items-center">
        <label class="col-md-2 col-form-label text-md-end fw-semibold" for="jb_booking_content"><{$smarty.const._MD_JILLBOOKIN_JB_BOOKING_CONTENT}></label>
        <div class="col-md-10">
          <input type="text" name="jb_booking_content" id="jb_booking_content" class="form-control" placeholder="<{$smarty.const._MD_JILLBOOKIN_JB_BOOKING_CONTENT}>">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-2 col-form-label text-md-end fw-semibold"><{$smarty.const._MD_TIME_WEEK}></div>
        <div class="col-md-10">
          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0">
              <caption class="visually-hidden"><{$smarty.const._MD_TIME_WEEK}></caption>
              <thead class="jb-table-head">
                <tr>
                  <th scope="col" class="th_style"><span class="visually-hidden"><{$smarty.const._MD_JSESSION}></span></th>
                  <{foreach from=$weeks item=week}>
                    <th scope="col" class="th_style"><{$week}></th>
                  <{/foreach}>
                </tr>
              </thead>
              <tbody>
                <{foreach from=$timeArr item=time}>
                  <tr>
                    <th scope="row" class="th_style"><{$time.jbt_title}></th>
                    <{foreach from=$weeks key=w item=week}>
                      <td class="text-center align-middle p-1">
                        <{if $time.open.$w}>
                          <label class="jb-check-label">
                            <input type="checkbox" class="form-check-input m-0" name="jb_week[<{$time.jbt_sn}>][]" value="<{$w}>" aria-label="<{$time.jbt_title}> <{$week}>">
                          </label>
                        <{else}>
                          <span class="jb-text-muted"><i class="fa-solid fa-minus" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._MD_EXPIRED}></span></span>
                        <{/if}>
                      </td>
                    <{/foreach}>
                  </tr>
                <{/foreach}>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="text-center mt-4">
        <input type="hidden" name="jbi_sn" value="<{$itemArr.jbi_sn}>">
        <{$token_form|default:''}>
        <input type="hidden" name="op" value="<{$next_op|default:''}>">
        <button type="submit" class="btn btn-primary px-4">
          <{$smarty.const._MD_SUMIT}>
        </button>
      </div>
    </form>
  </div>
<{/if}>
