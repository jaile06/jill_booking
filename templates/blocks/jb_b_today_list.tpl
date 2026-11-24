<style type="text/css" media="screen">
  .resp-vtabs .resp-tabs-container{
    min-height: <{$block.height}>px;
  }
</style>
<div class="jb-wrap">
  <div class="d-flex justify-content-end mb-2">
    <a href="<{$xoops_url}>/modules/jill_booking/index.php" class="jb-action jb-action-primary">
      <{$smarty.const._MB_JILLBOOKIN_MORE}> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
  </div>
  <div id="iteamtab<{$block.randStr}>" class="clearfix">
    <ul class="resp-tabs-list vert">
      <{foreach from=$block.content item=data}>
        <li><{$data.jbi_title}></li>
      <{/foreach}>
    </ul>
    <div class="resp-tabs-container vert">
      <{foreach from=$block.content item=data}>
        <div class="table-responsive">
          <table class="table table-sm table-hover table-bordered align-middle mb-0">
            <caption class="visually-hidden"><{$data.jbi_title}></caption>
            <thead class="jb-table-head">
              <tr>
                <th scope="col"><{$smarty.const._MB_JSESSION}></th>
                <th scope="col"><{$smarty.const._MB_JILLBOOKIN_JB_UID}></th>
              </tr>
            </thead>
            <tbody>
              <{if $data.todaylist|default:false}>
                <{foreach from=$data.todaylist item=tlist}>
                  <tr>
                    <th scope="row" class="text-center align-middle fw-medium"><{$tlist.jbt_title}></th>
                    <td class="text-center align-middle"><{$tlist.name}></td>
                  </tr>
                <{/foreach}>
              <{/if}>
            </tbody>
          </table>
        </div>
      <{/foreach}>
    </div>
  </div>
</div>
