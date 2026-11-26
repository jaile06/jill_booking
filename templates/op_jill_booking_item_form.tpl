<div class="container">
  <{$formValidator_code|default:''}>
  <div class="border rounded p-3 p-md-4 bg-white">
    <form action="<{$action|default:''}>" method="post" id="myForm">
      <div class="row mb-3 align-items-center">
        <label class="col-sm-2 col-form-label text-sm-end fw-semibold" for="jbi_title">
          <{$smarty.const._MA_JILLBOOKIN_JBI_TITLE}>
        </label>
        <div class="col-sm-10">
          <input type="text" name="jbi_title" id="jbi_title" class="form-control validate[required]" value="<{$jbi_title|default:''}>" placeholder="<{$smarty.const._MA_JILLBOOKIN_JBI_TITLE}>">
        </div>
      </div>

      <div class="row mb-3 align-items-center">
        <label class="col-sm-2 col-form-label text-sm-end fw-semibold" for="jbi_start">
          <{$smarty.const._MA_JILLBOOKIN_JBI_START}>
        </label>
        <div class="col-sm-4 mb-2 mb-sm-0">
          <div class="input-group">
            <input type="text" name="jbi_start" id="jbi_start" class="form-control" value="<{$jbi_start|default:''}>" onclick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d'})" placeholder="<{$smarty.const._MA_JILLBOOKIN_JBI_START}>">
            <button type="button" class="btn btn-outline-secondary" onclick="WdatePicker({el:'jbi_start', dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d'})" aria-label="<{$smarty.const._MA_JILLBOOKIN_JBI_START}>">
              <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        <label class="col-sm-2 col-form-label text-sm-end fw-semibold" for="jbi_end">
          <{$smarty.const._MA_JILLBOOKIN_JBI_END}>
        </label>
        <div class="col-sm-4">
          <div class="input-group">
            <input type="text" name="jbi_end" id="jbi_end" class="form-control" value="<{$jbi_end|default:''}>" onclick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d'})" placeholder="<{$smarty.const._MA_JILLBOOKIN_JBI_END}>">
            <button type="button" class="btn btn-outline-secondary" onclick="WdatePicker({el:'jbi_end', dateFmt:'yyyy-MM-dd', startDate:'%y-%M-%d'})" aria-label="<{$smarty.const._MA_JILLBOOKIN_JBI_END}>">
              <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </div>

      <fieldset class="row mb-3 align-items-center">
        <legend class="col-form-label col-sm-2 pt-0 text-sm-end fw-semibold fs-6">
          <{$smarty.const._MA_JILLBOOKIN_JBI_ENABLE}>
        </legend>
        <div class="col-sm-10">
          <div class="d-flex flex-wrap gap-3">
            <label class="jb-check-label gap-2" for="jbi_enable_1">
              <input class="form-check-input m-0" id="jbi_enable_1" type="radio" name="jbi_enable" value="1" <{if $jbi_enable == "1"}>checked="checked"<{/if}>>
              <span><{$smarty.const._YES}></span>
            </label>
            <label class="jb-check-label gap-2" for="jbi_enable_0">
              <input class="form-check-input m-0" id="jbi_enable_0" type="radio" name="jbi_enable" value="0" <{if $jbi_enable != "1"}>checked="checked"<{/if}>>
              <span><{$smarty.const._NO}></span>
            </label>
          </div>
        </div>
      </fieldset>

      <div class="row mb-3">
        <label class="col-sm-2 col-form-label text-sm-end fw-semibold" for="jbi_desc">
          <{$smarty.const._MA_JILLBOOKIN_JBI_DESC}>
        </label>
        <div class="col-sm-10">
          <{$Editor_code|default:''}>
        </div>
      </div>

      <div class="text-center mt-4">
        <input type="hidden" name="jbi_sn" value="<{$jbi_sn|default:''}>">
        <{* 不帶 id：後台佈景已有 #XOOPS_TOKEN_REQUEST *}>
        <input type="hidden" name="XOOPS_TOKEN_REQUEST" value="<{$token|default:''}>">
        <input type="hidden" name="op" value="<{$next_op|default:''}>">
        <button type="submit" class="btn btn-primary px-4">
          <{$smarty.const._TAD_SAVE}>
        </button>
      </div>
    </form>
  </div>
</div>
