<{if $now_op=="jbi_approval_form"}>
  <h3 class="text-center"><{$jbi_title|default:''}></h3>
  <div class="container">
    <form action="<{$action|default:''}>" method="post" id="myForm">
      <{$tmt_box|default:''}>
      <{$token_form|default:''}>
    </form>
  </div>
<{/if}>
