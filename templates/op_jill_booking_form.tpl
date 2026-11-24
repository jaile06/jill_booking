<select name="jbi_sn" id="jbi_sn" class="form-control form-select mb-3 w-auto" onchange="location.href='<{$action|default:''}>?jbi_sn='+this.value" aria-label="<{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}>">
  <option value=""><{$smarty.const._MD_JILLBOOKIN_CHOOSEITEM}></option>
  <{$item_opt|default:''}>
</select>

<{if $itemArr.jbi_sn|default:false}>
  <{$formValidator_code|default:''}>
  <h3 class="text-center"><{$itemArr.jbi_title}></h3>
  <hr>
  <form action="<{$action|default:''}>" method="post" id="myForm">
    <div class="row mb-2">
      <label class="col-sm-2 col-form-label text-md-end" for="jb_start_date"><{$smarty.const._MD_JILLBOOKIN_JB_DATE}></label>
      <div class="col-sm-10">
        <div class="row">
          <div class="col-sm-5">
            <input type="text" name="jb_start_date" id="jb_start_date" class="form-control" value="<{$jb_start_date|default:''}>" onclick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d', minDate:'<{$jb_start_date|default:''}>'<{if $max_date|default:false}>, maxDate:'<{$max_date}>'<{/if}>})" placeholder="<{$smarty.const._MD_JILLBOOKIN_JB_START_DATE}>" aria-label="<{$smarty.const._MD_JILLBOOKIN_JB_START_DATE}>">
          </div>
          <div class="col-sm-2 text-center"><i class="fa fa-arrow-right fa-2x" aria-hidden="true"></i></div>
          <div class="col-sm-5">
            <input type="text" name="jb_end_date" id="jb_end_date" class="form-control" value="<{$jb_end_date|default:''}>" onclick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d', minDate:'<{$jb_end_date|default:''}>'<{if $max_date|default:false}>, maxDate:'<{$max_date}>'<{/if}>})" placeholder="<{$smarty.const._MD_JILLBOOKIN_JB_END_DATE}>" aria-label="<{$smarty.const._MD_JILLBOOKIN_JB_END_DATE}>">
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-2">
      <label class="col-sm-2 col-form-label text-md-end" for="jb_booking_content"><{$smarty.const._MD_JILLBOOKIN_JB_BOOKING_CONTENT}></label>
      <div class="col-sm-10">
        <input type="text" name="jb_booking_content" id="jb_booking_content" class="form-control" placeholder="<{$smarty.const._MD_JILLBOOKIN_JB_BOOKING_CONTENT}>">
      </div>
    </div>
    <div class="row mb-2">
      <div class="col-sm-2 col-form-label text-md-end"><{$smarty.const._MD_TIME_WEEK}></div>
      <div class="col-sm-10 table-responsive">
        <table class="table table-striped table-hover table-sm">
          <thead>
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
                  <td class="text-center align-middle">
                    <{if $time.open.$w}>
                      <input type="checkbox" name="jb_week[<{$time.jbt_sn}>][]" value="<{$w}>" aria-label="<{$time.jbt_title}> <{$week}>">
                    <{else}>
                      <span class="jb_closed"><i class="fa fa-times" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._MD_EXPIRED}></span></span>
                    <{/if}>
                  </td>
                <{/foreach}>
              </tr>
            <{/foreach}>
          </tbody>
        </table>
      </div>
    </div>
    <div class="text-center">
      <input type="hidden" name="jbi_sn" value="<{$itemArr.jbi_sn}>">
      <{$token_form|default:''}>
      <input type="hidden" name="op" value="<{$next_op|default:''}>">
      <button type="submit" class="btn btn-primary"><{$smarty.const._MD_SUMIT}></button>
    </div>
  </form>
<{/if}>
