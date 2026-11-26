<div class="jb-wrap">
  <{if $now_op=="jbi_approval_form"}>
    <div class="container">
      <div class="border rounded p-3 p-md-4 bg-white">
        <h3 class="h4 border-bottom pb-2 mb-3"><{$jbi_title|default:''}></h3>
        <form action="<{$action|default:''}>" method="post" id="myForm">
          <{$tmt_box|default:''}>
          <{* 不帶 id：後台佈景已有 #XOOPS_TOKEN_REQUEST *}>
          <input type="hidden" name="XOOPS_TOKEN_REQUEST" value="<{$token|default:''}>">
        </form>
      </div>
    </div>
  <{/if}>
</div>
